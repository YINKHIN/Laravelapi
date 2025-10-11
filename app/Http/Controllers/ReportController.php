<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Import;
use App\Models\ImportDetail;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Staff;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class ReportController extends Controller
{
    /**
     * Get import report with filters
     */
    public function importReport(Request $request)
    {
        $query = Import::with(['staff', 'supplier', 'importDetails.product'])
            ->join('import_details', 'imports.id', '=', 'import_details.imp_code')
            ->join('staffs', 'imports.staff_id', '=', 'staffs.id')
            ->join('suppliers', 'imports.sup_id', '=', 'suppliers.id')
            ->join('products', 'import_details.pro_code', '=', 'products.id')
            ->select(
                'imports.id as import_id',
                'imports.imp_date',
                'staffs.full_name as staff_name',
                'suppliers.supplier as supplier_name',
                'products.pro_name as product_name',
                'import_details.qty',
                'import_details.price as unit_price',
                'import_details.amount',
                'imports.total as import_total'
            );

        // Apply filters
        if ($request->has('staff_id')) {
            $query->where('imports.staff_id', $request->staff_id);
        }
        if ($request->has('supplier_id')) {
            $query->where('imports.sup_id', $request->supplier_id);
        }
        if ($request->has('product_id')) {
            $query->where('import_details.pro_code', $request->product_id);
        }
        if ($request->has('date_from')) {
            $query->where('imports.imp_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('imports.imp_date', '<=', $request->date_to);
        }

        $reports = $query->orderBy('imports.imp_date', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }

    /**
     * Get sales report with filters
     */
    public function salesReport(Request $request)
    {
        $query = Order::with(['staff', 'customer', 'orderDetails.product'])
            ->join('order_details', 'orders.id', '=', 'order_details.ord_code')
            ->join('staffs', 'orders.staff_id', '=', 'staffs.id')
            ->join('customers', 'orders.cus_id', '=', 'customers.id')
            ->join('products', 'order_details.pro_code', '=', 'products.id')
            ->select(
                'orders.id as order_id',
                'orders.ord_date',
                'staffs.full_name as staff_name',
                'customers.cus_name as customer_name',
                'products.pro_name as product_name',
                'order_details.qty',
                'order_details.price as unit_price',
                'order_details.amount',
                'orders.total as order_total'
            );

        // Apply filters
        if ($request->has('staff_id')) {
            $query->where('orders.staff_id', $request->staff_id);
        }
        if ($request->has('customer_id')) {
            $query->where('orders.cus_id', $request->customer_id);
        }
        if ($request->has('product_id')) {
            $query->where('order_details.pro_code', $request->product_id);
        }
        if ($request->has('date_from')) {
            $query->where('orders.ord_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('orders.ord_date', '<=', $request->date_to);
        }

        $reports = $query->orderBy('orders.ord_date', 'desc')->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $reports
        ]);
    }

    /**
     * Get import summary
     */
    public function importSummary(Request $request)
    {
        $query = ImportDetail::join('imports', 'import_details.imp_code', '=', 'imports.id');

        if ($request->has('date_from')) {
            $query->where('imports.imp_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('imports.imp_date', '<=', $request->date_to);
        }

        $summary = $query->selectRaw('
            COUNT(DISTINCT imports.id) as total_imports,
            SUM(import_details.qty) as total_qty,
            SUM(import_details.amount) as total_amount
        ')->first();

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Get sales summary
     */
    public function salesSummary(Request $request)
    {
        $query = OrderDetail::join('orders', 'order_details.ord_code', '=', 'orders.id');

        if ($request->has('date_from')) {
            $query->where('orders.ord_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('orders.ord_date', '<=', $request->date_to);
        }

        $summary = $query->selectRaw('
            COUNT(DISTINCT orders.id) as total_orders,
            SUM(order_details.qty) as total_qty,
            SUM(order_details.amount) as total_amount
        ')->first();

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Export import report to Excel (CSV format)
     */
    public function exportImportExcel(Request $request)
    {
        $data = $this->getImportReportData($request);

        $filename = 'import_report_' . date('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Import ID', 'Date', 'Staff', 'Supplier', 'Product', 'Qty', 'Unit Price', 'Amount', 'Total']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->import_id,
                    $row->imp_date,
                    $row->staff_name,
                    $row->supplier_name,
                    $row->product_name,
                    $row->qty,
                    $row->unit_price,
                    $row->amount,
                    $row->import_total
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export sales report to Excel (CSV format)
     */
    public function exportSalesExcel(Request $request)
    {
        $data = $this->getSalesReportData($request);

        $filename = 'sales_report_' . date('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order ID', 'Date', 'Staff', 'Customer', 'Product', 'Qty', 'Unit Price', 'Amount', 'Total']);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->order_id,
                    $row->ord_date,
                    $row->staff_name,
                    $row->customer_name,
                    $row->product_name,
                    $row->qty,
                    $row->unit_price,
                    $row->amount,
                    $row->order_total
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getImportReportData($request)
    {
        $query = DB::table('imports')
            ->join('import_details', 'imports.id', '=', 'import_details.imp_code')
            ->join('staffs', 'imports.staff_id', '=', 'staffs.id')
            ->join('suppliers', 'imports.sup_id', '=', 'suppliers.id')
            ->join('products', 'import_details.pro_code', '=', 'products.id')
            ->select(
                'imports.id as import_id',
                'imports.imp_date',
                'staffs.full_name as staff_name',
                'suppliers.supplier as supplier_name',
                'products.pro_name as product_name',
                'import_details.qty',
                'import_details.price as unit_price',
                'import_details.amount',
                'imports.total as import_total'
            );

        // Apply same filters as report
        if ($request->has('staff_id')) {
            $query->where('imports.staff_id', $request->staff_id);
        }
        if ($request->has('supplier_id')) {
            $query->where('imports.sup_id', $request->supplier_id);
        }
        if ($request->has('product_id')) {
            $query->where('import_details.pro_code', $request->product_id);
        }
        if ($request->has('date_from')) {
            $query->where('imports.imp_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('imports.imp_date', '<=', $request->date_to);
        }

        return $query->orderBy('imports.imp_date', 'desc')->get();
    }

    private function getSalesReportData($request)
    {
        $query = DB::table('orders')
            ->join('order_details', 'orders.id', '=', 'order_details.ord_code')
            ->join('staffs', 'orders.staff_id', '=', 'staffs.id')
            ->join('customers', 'orders.cus_id', '=', 'customers.id')
            ->join('products', 'order_details.pro_code', '=', 'products.id')
            ->select(
                'orders.id as order_id',
                'orders.ord_date',
                'staffs.full_name as staff_name',
                'customers.cus_name as customer_name',
                'products.pro_name as product_name',
                'order_details.qty',
                'order_details.price as unit_price',
                'order_details.amount',
                'orders.total as order_total'
            );

        // Apply same filters as report
        if ($request->has('staff_id')) {
            $query->where('orders.staff_id', $request->staff_id);
        }
        if ($request->has('customer_id')) {
            $query->where('orders.cus_id', $request->customer_id);
        }
        if ($request->has('product_id')) {
            $query->where('order_details.pro_code', $request->product_id);
        }
        if ($request->has('date_from')) {
            $query->where('orders.ord_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('orders.ord_date', '<=', $request->date_to);
        }

        return $query->orderBy('orders.ord_date', 'desc')->get();
    }

    /**
     * Export import report to PDF
     */
    // public function exportImportPdf(Request $request)
    // {
    //     try {
    //         $data = $this->getImportReportData($request);
    //         $summary = $this->getImportSummaryData($request);
    //         $html = $this->generateImportPdfHtml($data, $summary, $request);
    //         $pdf = Pdf::loadHTML($html);
    //         $pdf->setPaper('A4', 'landscape');
    //         $filename = 'import_report_' . date('Y_m_d_H_i_s') . '.pdf';
    //         return $pdf->download($filename);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to generate PDF: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    public function exportImportPdf(Request $request)
    {
        try {
            $data = $this->getImportReportData($request);
            $summary = $this->getImportSummaryData($request);

            if (!$data->count() && !$summary) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data available for the selected filters.'
                ], 400);
            }

            $html = $this->generateImportPdfHtml($data, $summary, $request);

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'landscape');

            $filename = 'import_report_' . date('Y_m_d_H_i_s') . '.pdf';
            return $pdf->download($filename);  // Ensure this returns the file directly
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export sales report to PDF
     */
    public function exportSalesPdf(Request $request)
    {
        try {
            $data = $this->getSalesReportData($request);
            $summary = $this->getSalesSummaryData($request);

            $html = $this->generateSalesPdfHtml($data, $summary, $request);

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'landscape');

            $filename = 'sales_report_' . date('Y_m_d_H_i_s') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export single import record to Word
     */
    public function exportSingleImportWord(Request $request)
    {
        try {
            $importId = $request->get('import_id');
            if (!$importId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import ID is required'
                ], 400);
            }

            // Get single import record data
            $data = DB::table('imports')
                ->join('import_details', 'imports.id', '=', 'import_details.imp_code')
                ->join('staffs', 'imports.staff_id', '=', 'staffs.id')
                ->join('suppliers', 'imports.sup_id', '=', 'suppliers.id')
                ->join('products', 'import_details.pro_code', '=', 'products.id')
                ->select(
                    'imports.id as import_id',
                    'imports.imp_date',
                    'staffs.full_name as staff_name',
                    'suppliers.supplier as supplier_name',
                    'products.pro_name as product_name',
                    'import_details.qty',
                    'import_details.price as unit_price',
                    'import_details.amount',
                    'imports.total as import_total'
                )
                ->where('imports.id', $importId)
                ->first();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import record not found'
                ], 404);
            }

            $filename = $this->generateImportWordDocument($data, 'import');

            return response()->download($filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate Word document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export single sales record to Word
     */
    public function exportSingleSalesWord(Request $request)
    {
        try {
            $orderId = $request->get('order_id');
            if (!$orderId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order ID is required'
                ], 400);
            }

            // Get single sales record data
            $data = DB::table('orders')
                ->join('order_details', 'orders.id', '=', 'order_details.ord_code')
                ->join('staffs', 'orders.staff_id', '=', 'staffs.id')
                ->join('customers', 'orders.cus_id', '=', 'customers.id')
                ->join('products', 'order_details.pro_code', '=', 'products.id')
                ->select(
                    'orders.id as order_id',
                    'orders.ord_date',
                    'staffs.full_name as staff_name',
                    'customers.cus_name as customer_name',
                    'products.pro_name as product_name',
                    'order_details.qty',
                    'order_details.price as unit_price',
                    'order_details.amount',
                    'orders.total as order_total'
                )
                ->where('orders.id', $orderId)
                ->first();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales record not found'
                ], 404);
            }

            $filename = $this->generateImportWordDocument($data, 'sales');

            return response()->download($filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate Word document: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getImportSummaryData($request)
    {
        $query = ImportDetail::join('imports', 'import_details.imp_code', '=', 'imports.id');

        if ($request->has('date_from')) {
            $query->where('imports.imp_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('imports.imp_date', '<=', $request->date_to);
        }
        if ($request->has('staff_id')) {
            $query->where('imports.staff_id', $request->staff_id);
        }
        if ($request->has('supplier_id')) {
            $query->where('imports.sup_id', $request->supplier_id);
        }
        if ($request->has('product_id')) {
            $query->where('import_details.pro_code', $request->product_id);
        }

        return $query->selectRaw('
            COUNT(DISTINCT imports.id) as total_imports,
            SUM(import_details.qty) as total_qty,
            SUM(import_details.amount) as total_amount
        ')->first();
    }

    private function getSalesSummaryData($request)
    {
        $query = OrderDetail::join('orders', 'order_details.ord_code', '=', 'orders.id');

        if ($request->has('date_from')) {
            $query->where('orders.ord_date', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->where('orders.ord_date', '<=', $request->date_to);
        }
        if ($request->has('staff_id')) {
            $query->where('orders.staff_id', $request->staff_id);
        }
        if ($request->has('customer_id')) {
            $query->where('orders.cus_id', $request->customer_id);
        }
        if ($request->has('product_id')) {
            $query->where('order_details.pro_code', $request->product_id);
        }

        return $query->selectRaw('
            COUNT(DISTINCT orders.id) as total_orders,
            SUM(order_details.qty) as total_qty,
            SUM(order_details.amount) as total_amount
        ')->first();
    }

    private function generateImportPdfHtml($data, $summary, $request)
    {
        $dateRange = '';
        if ($request->has('date_from') && $request->has('date_to')) {
            $dateRange = 'From ' . $request->date_from . ' to ' . $request->date_to;
        } elseif ($request->has('date_from')) {
            $dateRange = 'From ' . $request->date_from;
        } elseif ($request->has('date_to')) {
            $dateRange = 'To ' . $request->date_to;
        }

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Import Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 30px; }
                .company { font-size: 18px; font-weight: bold; color: #333; }
                .title { font-size: 16px; font-weight: bold; margin: 10px 0; }
                .info { margin: 5px 0; color: #666; }
                .summary { background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .summary-item { display: inline-block; margin-right: 30px; }
                .summary-label { font-weight: bold; color: #333; }
                .summary-value { color: #007bff; font-size: 14px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .amount { text-align: right; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
                .no-data { text-align: center; color: #888; padding: 20px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="company">Inventory Management System</div>
                <div class="title">Import Report</div>
                <div class="info">Generated on: ' . date('Y-m-d H:i:s') . '</div>
                ' . ($dateRange ? '<div class="info">' . $dateRange . '</div>' : '') . '
            </div>
            
            <div class="summary">
                <div class="summary-item">
                    <div class="summary-label">Total Imports:</div>
                    <div class="summary-value">' . number_format($summary->total_imports ?? 0) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Quantity:</div>
                    <div class="summary-value">' . number_format($summary->total_qty ?? 0) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Amount:</div>
                    <div class="summary-value">$' . number_format($summary->total_amount ?? 0, 2) . '</div>
                </div>
            </div>
            
            ' . ($data->isEmpty() ? '<div class="no-data">No data available for the selected filters.</div>' : '
            <table>
                <thead>
                    <tr>
                        <th>Import ID</th>
                        <th>Date</th>
                        <th>Staff</th>
                        <th>Supplier</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>'
            . $data->map(function ($row) {
                return '
                        <tr>
                            <td>' . $row->import_id . '</td>
                            <td>' . $row->imp_date . '</td>
                            <td>' . htmlspecialchars($row->staff_name ?? '') . '</td>
                            <td>' . htmlspecialchars($row->supplier_name ?? '') . '</td>
                            <td>' . htmlspecialchars($row->product_name ?? '') . '</td>
                            <td>' . number_format($row->qty ?? 0) . '</td>
                            <td class="amount">$' . number_format($row->unit_price ?? 0, 2) . '</td>
                            <td class="amount">$' . number_format($row->amount ?? 0, 2) . '</td>
                        </tr>';
            })->join('') . '
                </tbody>
            </table>') . '
            
            <div class="footer">
                <p>This report was generated automatically by the Inventory Management System</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    private function generateSalesPdfHtml($data, $summary, $request)
    {
        $dateRange = '';
        if ($request->has('date_from') && $request->has('date_to')) {
            $dateRange = 'From ' . $request->date_from . ' to ' . $request->date_to;
        } elseif ($request->has('date_from')) {
            $dateRange = 'From ' . $request->date_from;
        } elseif ($request->has('date_to')) {
            $dateRange = 'To ' . $request->date_to;
        }

        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Sales Report</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 30px; }
                .company { font-size: 18px; font-weight: bold; color: #333; }
                .title { font-size: 16px; font-weight: bold; margin: 10px 0; }
                .info { margin: 5px 0; color: #666; }
                .summary { background: #f8f9fa; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .summary-item { display: inline-block; margin-right: 30px; }
                .summary-label { font-weight: bold; color: #333; }
                .summary-value { color: #007bff; font-size: 14px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .amount { text-align: right; }
                .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
                .no-data { text-align: center; color: #888; padding: 20px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="company">Inventory Management System</div>
                <div class="title">Sales Report</div>
                <div class="info">Generated on: ' . date('Y-m-d H:i:s') . '</div>
                ' . ($dateRange ? '<div class="info">' . $dateRange . '</div>' : '') . '
            </div>
            
            <div class="summary">
                <div class="summary-item">
                    <div class="summary-label">Total Orders:</div>
                    <div class="summary-value">' . number_format($summary->total_orders ?? 0) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Quantity:</div>
                    <div class="summary-value">' . number_format($summary->total_qty ?? 0) . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Total Amount:</div>
                    <div class="summary-value">$' . number_format($summary->total_amount ?? 0, 2) . '</div>
                </div>
            </div>
            
            ' . ($data->isEmpty() ? '<div class="no-data">No data available for the selected filters.</div>' : '
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Staff</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>'
            . $data->map(function ($row) {
                return '
                        <tr>
                            <td>' . $row->order_id . '</td>
                            <td>' . $row->ord_date . '</td>
                            <td>' . htmlspecialchars($row->staff_name ?? '') . '</td>
                            <td>' . htmlspecialchars($row->customer_name ?? '') . '</td>
                            <td>' . htmlspecialchars($row->product_name ?? '') . '</td>
                            <td>' . number_format($row->qty ?? 0) . '</td>
                            <td class="amount">$' . number_format($row->unit_price ?? 0, 2) . '</td>
                            <td class="amount">$' . number_format($row->amount ?? 0, 2) . '</td>
                        </tr>';
            })->join('') . '
                </tbody>
            </table>') . '
            
            <div class="footer">
                <p>This report was generated automatically by the Inventory Management System</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Generate Word document for single record
     */
    private function generateImportWordDocument($data, $type)
    {
        $phpWord = new PhpWord();

        // Add section
        $section = $phpWord->addSection([
            'marginLeft' => 600,
            'marginRight' => 600,
            'marginTop' => 600,
            'marginBottom' => 600,
        ]);

        // Title Style
        $titleStyle = ['name' => 'Arial', 'size' => 18, 'bold' => true, 'color' => '000080'];
        $headerStyle = ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => '333333'];
        $labelStyle = ['name' => 'Arial', 'size' => 11, 'bold' => true, 'color' => '666666'];
        $valueStyle = ['name' => 'Arial', 'size' => 11, 'color' => '000000'];
        $centerAlign = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];

        // Header
        $section->addText('Inventory Management System', $titleStyle, $centerAlign);
        $section->addText(ucfirst($type) . ' Transaction Details', $headerStyle, $centerAlign);
        $section->addText('Generated on: ' . date('Y-m-d H:i:s'), $valueStyle, $centerAlign);
        $section->addTextBreak(2);

        // Transaction Information Header
        $section->addText('TRANSACTION INFORMATION', $headerStyle);
        $section->addTextBreak(1);

        // Create table for transaction details
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'cccccc',
            'cellMargin' => 80
        ]);

        // Transaction ID
        $table->addRow();
        $table->addCell(3000)->addText('Transaction ID:', $labelStyle);
        $idValue = $type === 'import' ? $data->import_id : $data->order_id;
        $table->addCell(6000)->addText('#' . $idValue, $valueStyle);

        // Date
        $table->addRow();
        $table->addCell(3000)->addText('Date:', $labelStyle);
        $dateValue = $type === 'import' ? $data->imp_date : $data->ord_date;
        $table->addCell(6000)->addText(date('F d, Y', strtotime($dateValue)), $valueStyle);

        // Staff
        $table->addRow();
        $table->addCell(3000)->addText('Staff:', $labelStyle);
        $table->addCell(6000)->addText($data->staff_name, $valueStyle);

        // Supplier/Customer
        $table->addRow();
        $thirdPartyLabel = $type === 'import' ? 'Supplier:' : 'Customer:';
        $thirdPartyValue = $type === 'import' ? $data->supplier_name : $data->customer_name;
        $table->addCell(3000)->addText($thirdPartyLabel, $labelStyle);
        $table->addCell(6000)->addText($thirdPartyValue, $valueStyle);

        $section->addTextBreak(2);

        // Product Information Header
        $section->addText('PRODUCT INFORMATION', $headerStyle);
        $section->addTextBreak(1);

        // Product details table
        $productTable = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'cccccc',
            'cellMargin' => 80
        ]);

        $productTable->addRow();
        $productTable->addCell(3000)->addText('Product Name:', $labelStyle);
        $productTable->addCell(6000)->addText($data->product_name, $valueStyle);

        $productTable->addRow();
        $productTable->addCell(3000)->addText('Quantity:', $labelStyle);
        $productTable->addCell(6000)->addText(number_format($data->qty), $valueStyle);

        $productTable->addRow();
        $productTable->addCell(3000)->addText('Unit Price:', $labelStyle);
        $productTable->addCell(6000)->addText('$' . number_format($data->unit_price, 2), $valueStyle);

        $productTable->addRow();
        $productTable->addCell(3000)->addText('Total Amount:', $labelStyle);
        $productTable->addCell(6000)->addText('$' . number_format($data->amount, 2), ['name' => 'Arial', 'size' => 12, 'bold' => true, 'color' => '008000']);

        $section->addTextBreak(2);

        // Summary Section
        $section->addText('TRANSACTION SUMMARY', $headerStyle);
        $section->addTextBreak(1);

        $summaryTable = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'cccccc',
            'cellMargin' => 80
        ]);

        $summaryTable->addRow();
        $summaryTable->addCell(3000)->addText('Transaction Type:', $labelStyle);
        $summaryTable->addCell(6000)->addText(ucfirst($type) . ' Transaction', $valueStyle);

        $summaryTable->addRow();
        $summaryTable->addCell(3000)->addText('Total Quantity:', $labelStyle);
        $summaryTable->addCell(6000)->addText(number_format($data->qty) . ' items', $valueStyle);

        $summaryTable->addRow();
        $summaryTable->addCell(3000)->addText('Grand Total:', $labelStyle);
        $summaryTable->addCell(6000)->addText('$' . number_format($data->amount, 2), ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => 'FF0000']);

        $section->addTextBreak(2);

        // Footer
        $section->addText('This document was generated automatically by the Inventory Management System',
            ['name' => 'Arial', 'size' => 9, 'italic' => true, 'color' => '666666'],
            $centerAlign);

        // Generate filename and save
        $filename = storage_path('app/temp/' . $type . '_transaction_' . $idValue . '_' . date('Y_m_d_H_i_s') . '.docx');

        // Ensure temp directory exists
        if (!file_exists(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        // Save document
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filename);

        return $filename;
    }
}

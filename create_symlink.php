<?php
// This script creates the storage symlink manually
// Run this script from the project root directory

$target = __DIR__ . '/storage/app/public';
$link = __DIR__ . '/public/storage';

// Check if the target directory exists
if (!file_exists($target)) {
    echo "Target directory does not exist: $target\n";
    echo "Creating the directory...\n";
    mkdir($target, 0755, true);
}

// Check if symlink already exists
if (is_link($link) || file_exists($link)) {
    echo "Symlink or file already exists at: $link\n";
    echo "Removing existing link/file...\n";
    if (is_link($link)) {
        unlink($link);
    } else {
        rmdir($link);
    }
}

// Create the symlink
echo "Creating symlink...\n";
echo "From: $target\n";
echo "To: $link\n";

if (symlink($target, $link)) {
    echo "Symlink created successfully!\n";
} else {
    echo "Failed to create symlink.\n";
    echo "You may need to run this script with administrator privileges.\n";
    
    // Try to create a junction on Windows as an alternative
    echo "Trying to create junction (Windows alternative)...\n";
    $cmd = "mklink /J \"$link\" \"$target\"";
    echo "Running command: $cmd\n";
    system($cmd, $retval);
    if ($retval === 0) {
        echo "Junction created successfully!\n";
    } else {
        echo "Failed to create junction.\n";
    }
}
?>
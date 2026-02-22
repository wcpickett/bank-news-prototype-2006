<?php
/**
 * Header Include
 * HTML head, navigation, and opening tags
 * 
 * Expects these variables to be set:
 *   $pageTitle - Page title for <title> tag
 *   $pdo - Database connection (optional, for nav state)
 */

$pageTitle = $pageTitle ?? 'Bank Directory';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> | Bank Directory</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="site-title">
                    <a href="search.php">Bank Directory</a>
                </div>
                <nav class="main-nav">
                    <a href="search.php">Search</a>
                </nav>
            </div>
        </div>
    </header>
    
    <main class="site-main">
        <div class="container">

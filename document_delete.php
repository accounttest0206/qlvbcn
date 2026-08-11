<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Document.php';

Auth::requireLogin();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $docModel = new Document();
    $docModel->delete($id);
}

header('Location: documents.php');
exit;

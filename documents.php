<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Document.php';

Auth::requireLogin();
$user = Auth::user();
$docModel = new Document();
$db = Database::getInstance();

// Lấy danh sách Categories cho filter dropdown
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");

// Đọc bộ lọc từ GET
$filters = [
    'keyword'     => trim($_GET['keyword'] ?? ''),
    'doc_type'    => trim($_GET['doc_type'] ?? ''),
    'status'      => trim($_GET['status'] ?? ''),
    'category_id' => trim($_GET['category_id'] ?? ''),
    'date_from'   => trim($_GET['date_from'] ?? ''),
    'date_to'     => trim($_GET['date_to'] ?? '')
];

$documents = $docModel->getAll($user['id'], $user['role'], $filters);
$pageTitle = 'Quản Lý Văn Bản';

require_once __DIR__ . '/views/header.php';
?>

<!-- Title & Add Button -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Danh Sách Văn Bản</h3>
        <p class="text-muted mb-0">Quản lý, tìm kiếm và cập nhật tiến độ các văn bản lưu / thực hiện</p>
    </div>
    <a href="document_add.php" class="btn btn-primary fw-semibold">
        <i class="bi bi-plus-circle me-1"></i> Thêm Văn Bản Mới
    </a>
</div>

<!-- Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="documents.php" class="row g-3">
            <!-- Keyword search -->
            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold small">Từ khóa tìm kiếm</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="keyword" class="form-control" placeholder="Tiêu đề, số hiệu, trích yếu..." value="<?= htmlspecialchars($filters['keyword']) ?>">
                </div>
            </div>

            <!-- Doc Type -->
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label fw-semibold small">Phân loại</label>
                <select name="doc_type" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="luu" <?= $filters['doc_type'] === 'luu' ? 'selected' : '' ?>>Văn bản lưu</option>
                    <option value="thuc_hien" <?= $filters['doc_type'] === 'thuc_hien' ? 'selected' : '' ?>>Văn bản thực hiện</option>
                </select>
            </div>

            <!-- Status -->
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label fw-semibold small">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="chua_xu_ly" <?= $filters['status'] === 'chua_xu_ly' ? 'selected' : '' ?>>Chưa xử lý</option>
                    <option value="dang_thuc_hien" <?= $filters['status'] === 'dang_thuc_hien' ? 'selected' : '' ?>>Đang thực hiện</option>
                    <option value="hoan_thanh" <?= $filters['status'] === 'hoan_thanh' ? 'selected' : '' ?>>Hoàn thành</option>
                </select>
            </div>

            <!-- Category -->
            <div class="col-12 col-sm-6 col-md-2">
                <label class="form-label fw-semibold small">Danh mục</label>
                <select name="category_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $filters['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date Range -->
            <div class="col-12 col-sm-6 col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Lọc
                </button>
                <a href="documents.php" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Documents Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">STT</th>
                        <th>Số Hiệu & Tiêu Đề</th>
                        <th>Danh Mục</th>
                        <th>Loại</th>
                        <th>Trạng Thái & Tiến Độ</th>
                        <th>Ngày Ban Hành</th>
                        <th class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Không tìm thấy văn bản phù hợp với bộ lọc.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documents as $idx => $doc): ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-bold text-body mb-1">
                                        <a href="document_detail.php?id=<?= $doc['id'] ?>" class="text-decoration-none text-body hover-primary">
                                            <?= htmlspecialchars($doc['title']) ?>
                                        </a>
                                    </div>
                                    <div class="small text-muted d-flex gap-2 align-items-center">
                                        <span class="badge bg-light text-dark border"><i class="bi bi-hash"></i> <?= htmlspecialchars($doc['doc_number']) ?></span>
                                        <?php if ($doc['file_type']): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border">
                                                <i class="bi bi-file-earmark"></i> .<?= strtoupper($doc['file_type']) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-body-tertiary text-body border">
                                        <?= htmlspecialchars($doc['category_name'] ?? 'Chưa phân loại') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($doc['doc_type'] === 'luu'): ?>
                                        <span class="badge bg-primary">Văn bản lưu</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Thực hiện</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($doc['status'] === 'hoan_thanh'): ?>
                                        <span class="badge bg-success mb-1"><i class="bi bi-check-lg"></i> Hoàn thành</span>
                                    <?php elseif ($doc['status'] === 'dang_thuc_hien'): ?>
                                        <span class="badge bg-info text-dark mb-1">Đang thực hiện</span>
                                        <div class="progress" style="height: 6px; width: 100px;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $doc['progress'] ?>%"></div>
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;"><?= $doc['progress'] ?>% tiến độ</small>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Chưa xử lý</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?= $doc['issued_date'] ? date('d/m/Y', strtotime($doc['issued_date'])) : 'N/A' ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="document_detail.php?id=<?= $doc['id'] ?>" class="btn btn-outline-primary" title="Xem chi tiết & Preview">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="document_edit.php?id=<?= $doc['id'] ?>" class="btn btn-outline-secondary" title="Sửa văn bản">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="document_delete.php?id=<?= $doc['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa văn bản này?');" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/footer.php'; ?>

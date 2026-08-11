<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Document.php';

Auth::requireLogin();
$user = Auth::user();
$docModel = new Document();

$stats = $docModel->getDashboardStats($user['id'], $user['role']);
$pageTitle = 'Dashboard - Tổng Quan';

require_once __DIR__ . '/views/header.php';
?>

<!-- Page Header Banner -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Tổng Quan Hệ Thống</h3>
        <p class="text-muted mb-0">Xin chào, <strong><?= htmlspecialchars($user['fullname']) ?></strong>! Dưới đây là thống kê quản lý văn bản cá nhân của bạn.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="document_add.php" class="btn btn-primary fw-semibold">
            <i class="bi bi-file-earmark-plus me-1"></i> Thêm Văn Bản Mới
        </a>
        <a href="documents.php" class="btn btn-outline-secondary fw-semibold">
            <i class="bi bi-list-task me-1"></i> Tất Cả Văn Bản
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1: Tổng số văn bản -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 bg-body">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                    <i class="bi bi-files fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted fw-normal mb-1">Tổng Số Văn Bản</h6>
                    <h3 class="fw-bold mb-0"><?= $stats['total'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Số văn bản lưu -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 bg-body">
            <div class="card-body d-flex align-items-center">
                <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                    <i class="bi bi-archive-fill fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted fw-normal mb-1">Văn Bản Lưu (Tham Khảo)</h6>
                    <h3 class="fw-bold mb-0 text-info"><?= $stats['luu'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Số văn bản đang thực hiện -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 bg-body">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                    <i class="bi bi-hourglass-split fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted fw-normal mb-1">Đang Thực Hiện / Xử Lý</h6>
                    <h3 class="fw-bold mb-0 text-warning"><?= $stats['dang_thuc_hien'] + $stats['chua_xu_ly'] ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 4: Số văn bản hoàn thành -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 bg-body">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                    <i class="bi bi-check-circle-fill fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted fw-normal mb-1">Đã Hoàn Thành</h6>
                    <h3 class="fw-bold mb-0 text-success"><?= $stats['hoan_thanh'] ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <!-- Chart 1: Phân bổ Trạng thái -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-pie-chart-fill text-primary me-2"></i>Trạng Thái Văn Bản</span>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="width: 100%; max-width: 280px; height: 250px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 2: Thống kê theo Danh mục -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart-line-fill text-success me-2"></i>Phân Loại Theo Danh Mục</span>
            </div>
            <div class="card-body">
                <div style="width: 100%; height: 250px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="row g-3 mb-4">
    <!-- Table 1: Văn bản mới cập nhật -->
    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 fw-bold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history text-primary me-2"></i>Văn Bản Mới Cập Nhật</span>
                <a href="documents.php" class="btn btn-sm btn-link text-decoration-none">Xem tất cả &raquo;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Số Hiệu & Tiêu Đề</th>
                                <th>Loại</th>
                                <th>Trạng Thái / Tiến Độ</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stats['recent_docs'])): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Chưa có văn bản nào.</td></tr>
                            <?php else: ?>
                                <?php foreach ($stats['recent_docs'] as $doc): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-truncate" style="max-width: 260px;">
                                                <a href="document_detail.php?id=<?= $doc['id'] ?>" class="text-decoration-none text-body">
                                                    <?= htmlspecialchars($doc['title']) ?>
                                                </a>
                                            </div>
                                            <small class="text-muted"><i class="bi bi-hash"></i> <?= htmlspecialchars($doc['doc_number']) ?></small>
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
                                                <span class="badge bg-success mb-1">Hoàn thành</span>
                                            <?php elseif ($doc['status'] === 'dang_thuc_hien'): ?>
                                                <span class="badge bg-info text-dark mb-1">Đang xử lý</span>
                                                <div class="progress" style="height: 6px; width: 80px;">
                                                    <div class="progress-bar bg-info" style="width: <?= $doc['progress'] ?>%"></div>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge bg-secondary mb-1">Chưa xử lý</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="document_detail.php?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Table 2: Văn bản sắp / đến hạn -->
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0 pt-3 fw-bold d-flex justify-content-between align-items-center text-danger">
                <span><i class="bi bi-calendar-event-fill me-2"></i>Cần Xử Lý / Sắp Đến Hạn</span>
            </div>
            <div class="card-body p-3">
                <?php if (empty($stats['upcoming_docs'])): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle text-success fs-2 d-block mb-2"></i>
                        Không có văn bản nào sắp đến hạn cần xử lý!
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($stats['upcoming_docs'] as $uDoc): ?>
                            <a href="document_detail.php?id=<?= $uDoc['id'] ?>" class="list-group-item list-group-item-action border-0 mb-2 rounded bg-body-tertiary">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-truncate" style="max-width: 220px;"><?= htmlspecialchars($uDoc['title']) ?></span>
                                    <span class="badge bg-danger">Hạn: <?= date('d/m/Y', strtotime($uDoc['due_date'])) ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small text-muted">
                                    <span><i class="bi bi-hash"></i> <?= htmlspecialchars($uDoc['doc_number']) ?></span>
                                    <span class="text-warning fw-semibold">Tiến độ: <?= $uDoc['progress'] ?>%</span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart Script Init -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Chart Trạng thái
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Chưa xử lý', 'Đang thực hiện', 'Hoàn thành'],
            datasets: [{
                data: [<?= $stats['chua_xu_ly'] ?>, <?= $stats['dang_thuc_hien'] ?>, <?= $stats['hoan_thanh'] ?>],
                backgroundColor: ['#6c757d', '#0dcaf0', '#198754']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // 2. Chart Danh mục
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    const catLabels = <?= json_encode(array_column($stats['cat_stats'], 'name')) ?>;
    const catData = <?= json_encode(array_column($stats['cat_stats'], 'count')) ?>;

    new Chart(catCtx, {
        type: 'bar',
        data: {
            labels: catLabels,
            datasets: [{
                label: 'Số lượng văn bản',
                data: catData,
                backgroundColor: '#0d6efd',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>

<?php require_once __DIR__ . '/views/footer.php'; ?>

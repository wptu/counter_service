<?php
/**
 * Month Selection View
 */
?>
<style>
    .month-selection-header {
        text-align: center;
        margin-bottom: 40px;
        color: #37474f;
    }

    .month-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        padding: 10px;
    }

    .month-card {
        background: white;
        border: 1px solid #eceff1;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        text-decoration: none;
        color: #546e7a;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 15px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        position: relative;
        overflow: hidden;
    }

    .month-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.06);
        border-color: #cfd8dc;
        color: #37474f;
    }

    /* Minimalist Calendar Icon using CSS */
    .icon-calendar-minimal {
        width: 40px;
        height: 40px;
        border: 2px solid #b0bec5;
        border-radius: 6px;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Sarabun', sans-serif;
        font-weight: 700;
        font-size: 1.2rem;
        color: #b0bec5;
        background: white;
        transition: all 0.3s ease;
    }

    .icon-calendar-minimal::before {
        content: '';
        position: absolute;
        top: -4px;
        left: 8px;
        width: 2px;
        height: 6px;
        background: #b0bec5;
        border-radius: 2px;
        transition: background 0.3s ease;
    }

    .icon-calendar-minimal::after {
        content: '';
        position: absolute;
        top: -4px;
        right: 8px;
        width: 2px;
        height: 6px;
        background: #b0bec5;
        border-radius: 2px;
        transition: background 0.3s ease;
    }

    .month-card:hover .icon-calendar-minimal {
        border-color: #546e7a;
        color: #546e7a;
    }

    .month-card:hover .icon-calendar-minimal::before,
    .month-card:hover .icon-calendar-minimal::after {
        background: #546e7a;
    }

    .month-name {
        font-size: 1.2rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .month-label {
        font-size: 0.8rem;
        color: #90a4ae;
        font-weight: 500;
        text-transform: uppercase;
    }
</style>

<div class="month-selection-header">
    <h2>เลือกเดือนที่ต้องการดู</h2>
</div>

<div class="month-grid">
    <?php
    for ($m = 0; $m < 12; $m++) {
        $monthName = THAI_MONTHS[$m];
        $monthShort = THAI_MONTHS_SHORT[$m];
        ?>
        <a href="?page=calendars&month=<?= $m ?>" class="month-card">
            <div class="icon-calendar-minimal">
                <?= $m + 1 ?>
            </div>
            <div>
                <div class="month-name"><?= $monthName ?></div>
            </div>
        </a>
    <?php } ?>
</div>
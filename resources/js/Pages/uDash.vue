<template>
    <div
        class="row gap-2 d-flex flex-column align-items-center justify-content-center mb-2"
    >
        <div class="col-11 p-0 mb-4">
            <!-- Header -->
            <div
                class="header-wrapper p-3 p-md-5 rounded-4 d-flex flex-column flex-md-row justify-content-between shadow-unix fade-in"
            >
                <div class="d-flex gap-2">
                    <div
                        class="icon-container glassMorph rounded-4 d-flex justify-content-center align-items-center"
                    >
                        <i
                            class="bi bi-clock-history fs-1 d-flex justify-content-center align-items-center text-white"
                        ></i>
                    </div>
                    <div>
                        <div class="title-header fs-3 fw-bold m-0 p-0 title">
                            Employee Overtime Management
                        </div>
                        <div class="text-white sub-title-header fs-6">
                            Kelola jadwal Lembur karyawan dengan mudah
                        </div>
                    </div>
                </div>
                <div
                    class="d-flex flex-md-column align-items-center align-items-md-end justify-content-center gap-2 button-header"
                >
                    <div
                        class="px-md-4 px-2 py-1 glassMorph rounded-3 fw-bold text-white"
                    >
                        <i
                            v-if="dayNight == 'Siang'"
                            class="bi bi-cloud-sun-fill me-2 fs-4 fade-in"
                        ></i>
                        <i
                            v-else
                            class="bi bi-cloud-moon-fill me-2 fs-4 fade-in"
                        ></i>
                        13 Jun 2025
                    </div>
                    <button
                        @click="AssignOvertime = true"
                        class="btn btn-primary py-2 px-2 shiningEffect"
                    >
                        Assign Overtime
                    </button>
                </div>
            </div>
        </div>
        <!-- Body -->

        <div class="col-11 p-0">
            <div class="row gx-2">
                <div class="col-md-4">
                    <div class="bg-white rounded-4 p-4 cardUser">1</div>
                </div>
                <div class="col-md-4">
                    <div class="bg-white rounded-4 p-4 cardUser">1</div>
                </div>
                <div class="col-md-4">
                    <div class="bg-white rounded-4 p-4 cardUser">1</div>
                </div>
            </div>
        </div>

        <div class="col-11 p-0">
            <div class="row gx-2">
                <div class="col-md-8">
                    <div class="bg-white rounded-4 p-4 cardUser">1</div>
                </div>
                <div class="col-md-4">
                    <div class="bg-white rounded-4 p-4 cardUser">1</div>
                </div>
            </div>
        </div>

        <div class="col-11 p-0">
            <div class="bg-white rounded-4 p-4 cardUser">1</div>
        </div>

        <div class="col-11 p-0 table-container rounded-4 shadow-unix2 fade-in">
            <div class="table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th
                                class="sticky-column employee-header"
                                style="max-width: fit-content"
                            >
                                <i class="bi bi-columns me-2"></i>
                                Task
                                <small class="employee-count">(1)</small>
                            </th>
                            <th>
                                <div class="header-info">
                                    <div class="day-name">Start</div>
                                </div>
                                <div class="day-indicator"></div>
                            </th>
                            <th>
                                <div class="header-info">
                                    <div class="day-name">End</div>
                                </div>
                                <div class="day-indicator"></div>
                            </th>
                            <th>
                                <div class="header-info">
                                    <div class="day-name">Status</div>
                                </div>
                                <div class="day-indicator"></div>
                            </th>
                            <th>
                                <div class="header-info">
                                    <div class="day-name">Progress</div>
                                </div>
                                <div class="day-indicator"></div>
                            </th>
                        </tr>
                    </thead>
                    <!-- error -->
                    <tbody>
                        <tr class="employee-row">
                            <td
                                class="sticky-column employee-cell me-1"
                                style="max-width: fit-content"
                            >
                                <div class="employee-info">
                                    <div class="employee-avatar">TS</div>
                                    <div class="employee-details">
                                        <div class="employee-name">
                                            Task Judul
                                        </div>
                                        <!-- <div class="employee-id">
                                                K.Perusahaan:
                                                {{ item.Kode_Perusahaan }}
                                            </div> -->
                                    </div>
                                </div>
                            </td>
                            <td class="shift-cell">
                                <div class="shift-container">
                                    <div class="shift-content">
                                        <div class="shift-name">Task1</div>
                                    </div>
                                </div>
                            </td>
                            <td class="shift-cell">
                                <div class="shift-container">
                                    <div class="shift-content">
                                        <div class="shift-name">Task1</div>
                                    </div>
                                </div>
                            </td>
                            <td class="shift-cell">
                                <div class="shift-container">
                                    <div class="shift-content">
                                        <div class="shift-name">Task1</div>
                                    </div>
                                </div>
                            </td>
                            <td class="shift-cell">
                                <div class="shift-container">
                                    <div class="shift-content">
                                        <div class="shift-name">Task1</div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-container p-3" v-if="totalPages > 1">
                <button
                    class="page-btn"
                    @click="prevPage"
                    :disabled="currentPage === 1"
                >
                    <i
                        class="bi bi-chevron-left d-flex justify-content-center align-items-center"
                    ></i>
                </button>
                <span class="page-info">
                    {{ currentPage }} / {{ totalPages }}
                </span>
                <button
                    class="page-btn"
                    @click="nextPage"
                    :disabled="currentPage === totalPages"
                >
                    <i
                        class="bi bi-chevron-right d-flex justify-content-center align-items-center"
                    ></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: String,
    },
};
</script>

<style>
*,
*::before,
*::after {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --secondary: #64748b;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --info: #06b6d4;
    --light: #f8fafc;
    --dark: #1e293b;
    --surface: #ffffff;
    --surface-soft: #f1f5f9;
    --border: #e2e8f0;
    --text: #334155;
    --text-muted: #64748b;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-inset: inset 0 0px 0.3rem -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-lg-inset: inset 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --gradient-success: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --gradient-warning: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --gradient-info: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    --gradient-dark: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
}
* {
    box-sizing: border-box;
}

.cardUser {
    box-shadow: var(--shadow);
    transition: all 0.3s ease;
}
.cardUser:hover {
    box-shadow: var(--shadow-lg);
}

/* detailsModal */

/* timepicker */
.el-input__wrapper {
    flex-grow: 0;
    /* justify-content: start; */
}

.el-input__wrapper {
    width: 10px;
    border: none;
    box-shadow: none;
    left: -5rem;
    /* opacity: 0; */
    position: relative;
}
.hidden-datepicker {
    position: absolute;
    width: 0;
    height: 0;
    padding: 0;
    margin: 0;
    border: 0;
    opacity: 0;
    pointer-events: none;
}
.el-input__suffix-inner {
    display: none;
}
.el-input__wrapper.is-focus {
    box-shadow: none;
}
.el-popper.is-pure {
    margin-top: 0rem;
    position: relative;
    margin-left: -4.5rem;
}
.el-input__prefix-inner {
    display: none;
}
.el-input__wrapper:hover {
    box-shadow: none;
}
.el-date-editor.el-input,
.el-date-editor.el-input__wrapper {
    width: 0;
}
.el-input__inner {
    width: 100%;
    position: absolute;
}
.custom-time-picker {
    height: max-content;
}

.custom-time-picker .el-input__inner {
    width: 9rem;
    opacity: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border);
    border-radius: 10px;
    background: var(--surface);
    color: var(--text);
    font-size: 0.95rem;
    transition: all 0.3s ease;
    height: 2rem;
}
.custom-time-picker .el-input--suffix .el-input__inner {
    padding-right: 0px;
    padding-left: 2rem;
}
.user.custom-time-picker .el-input__inner {
    width: 40%;
    left: 0;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border);
    border-radius: 12px;
    background: var(--surface);
    color: var(--text);
    font-size: 0.95rem;
    transition: all 0.3s ease;
    height: 52px;
}
.user2.custom-time-picker .el-input__inner {
    width: 68%;
    left: 0;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border);
    border-radius: 12px;
    background: var(--surface);
    color: var(--text);
    font-size: 0.95rem;
    transition: all 0.3s ease;
    height: 52px;
}

.custom-time-picker .el-input__inner:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.custom-time-picker .el-input--suffix .el-input__inner {
    padding-right: 40px;
}

.custom-time-picker .el-input__suffix {
    right: 10px;
}

.custom-time-picker .el-input__icon {
    color: var(--primary);
}

/* Fix z-index for time picker dropdown */
.el-time-panel {
    z-index: 10000 !important;
}

.el-picker-panel {
    z-index: 10000 !important;
}

.el-popper {
    z-index: 10000 !important;
}

.header-wrapper {
    min-height: 140px;

    background: var(--gradient-primary);
}
.title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    letter-spacing: -0.025em;
    color: var(--text);
}
.shadow-unix {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.glassMorph {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10rem);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}
.glassMorph:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}
.shiningEffect {
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
}

.shiningEffect::before {
    content: "";
    position: absolute;
    top: 0%;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.2),
        transparent
    );
    transition: left 0.5s;
}

.shiningEffect:hover::before {
    left: 100%;
}
.btn-primary {
    background-color: var(--primary);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(20px);
}

/* Search and Filters */
.search-filter-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-container {
    position: relative;
    flex: 1;
    flex-grow: 1;
    min-width: 280px;
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    z-index: 2;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 2px solid var(--border);
    border-radius: 12px;
    background: var(--surface);
    color: var(--text);
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-results-count {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: var(--primary);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.filters-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Filter Dropdown */
.filter-dropdown {
    position: relative;
}

.filter-btn {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: var(--surface-soft);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--text);
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 160px;
    justify-content: space-between;
}

.filter-btn:hover {
    background: var(--surface);
    border-color: var(--primary);
}

.filter-btn .arrow {
    transition: transform 0.3s ease;
}

.filter-btn .arrow.rotate {
    transform: rotate(180deg);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    box-shadow: var(--shadow-lg);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    max-height: 200px;
    overflow-y: auto;
}

.dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.dropdown-item:hover {
    background: var(--surface-soft);
}

.dropdown-item i.bi-check {
    color: var(--primary);
    font-weight: bold;
}

/* Table */

.table-container {
    overflow: hidden;
}

.table-wrapper {
    overflow-x: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--border) transparent;
}

.table-wrapper::-webkit-scrollbar {
    height: 8px;
}

.table-wrapper::-webkit-scrollbar-track {
    background: var(--surface-soft);
}

.table-wrapper::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 0px;
}

.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.9rem;
}

.modern-table thead th {
    background: var(--surface-soft);
    padding: 1.25rem 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--text);
    border-bottom: 2px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 10;
}

.sticky-column {
    position: sticky;
    left: 0;
    background: var(--surface);
    z-index: 11;
    /* box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); */
}

.employee-header {
    min-width: 200px;
    z-index: 2050;
    position: relative;
    /* background-color: rgba(239, 68, 68, 0.05); */
    border-right: 1px solid var(--border);
}

/* ini Baru */

.employee-count {
    color: var(--text-muted);
    font-weight: 400;
}

.day-header {
    min-width: 100px;
    text-align: center;
    /* position: relative; */
}

.day-header.today {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
}

.header-info {
    display: flex;
    text-align: center;
    flex-direction: column;
    gap: 0.25rem;
}

.day-name {
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.8rem;
}

.day-date {
    font-size: 0.85rem;
    opacity: 0.8;
}

.day-indicator {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 30px;
    height: 3px;
    background: white;
    border-radius: 2px;
}

.employee-row {
    transition: all 0.3s ease;
    cursor: pointer;
    background: var(--surface);
}

.employee-row:hover {
    background: var(--surface-soft);
}

/* Try this if the hover is being overridden by .highlight */
.employee-row.highlight:hover {
    /* More specific */
    background: var(--surface-soft);
}

.employee-row.highlight {
    border-left: 4px solid var(--danger);
}

.employee-cell {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    border-right: 1px solid var(--border);
}

.employee-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.employee-avatar {
    width: 28px;
    height: 28px;
    border-radius: 5px;
    background: var(--gradient-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.85rem;
}

.employee-avatar.large {
    width: 60px;
    height: 60px;
    font-size: 1.1rem;
}

.employee-details {
    display: flex;
    flex-direction: column;
    gap: 0rem;
}

.employee-name {
    font-weight: 600;
    color: var(--text);
}

.employee-id {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.shift-cell {
    padding: 0.5rem;
    /* background: var(--surface); */
    border-bottom: 1px solid var(--border);
    text-align: center;
}

.shift-container {
    padding: 0.75rem;
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.shift-container.interactive:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.shift-container.today {
    border: 2px solid var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.shift-content {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.25rem;
}

.shift-name {
    font-weight: 600;
    font-size: 0.85rem;
}

.edit-icon {
    opacity: 0;
    transition: opacity 0.3s ease;
    font-size: 0.8rem;
}

.shift-container:hover .edit-icon {
    opacity: 1;
}

.shift-time {
    font-size: 0.75rem;
    opacity: 0.8;
}

/* Shift Status Colors */
.shift-morning {
    background: linear-gradient(135deg, #fef3c7, #fcd34d);
    color: #92400e;
}

.shift-afternoon {
    background: linear-gradient(135deg, #ddd6fe, #a78bfa);
    color: #5b21b6;
}

.shift-night {
    background: linear-gradient(135deg, #1f2937, #374151);
    color: white;
}

.shift-noshift {
    background: var(--surface-soft);
    color: var(--text-muted);
    border: 1px dashed var(--border);
}

/* Pagination */
.pagination-container {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    padding: 0 1.5rem;
}

.page-btn {
    width: 40px;
    height: 40px;
    border: 1px solid var(--border);
    background: var(--surface);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.page-btn:hover:not(:disabled) {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    font-weight: 600;
    color: var(--text);
    min-width: 80px;
    text-align: center;
}

/* Card View */
.card-view {
    padding: 1.5rem;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.employee-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.employee-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--primary);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}

.card-header h4 {
    margin: 0 0 0.25rem 0;
    font-weight: 600;
    color: var(--text);
}

.card-header p {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.card-shifts {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.card-shift-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem;
    background: var(--surface-soft);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.card-shift-item:hover {
    background: var(--primary);
    color: white;
    transform: translateX(4px);
}

.shift-day {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.shift-day span {
    font-weight: 600;
    font-size: 0.9rem;
}

.shift-day small {
    font-size: 0.8rem;
    opacity: 0.8;
}

.shift-badge-card {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-muted);
}

.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-state h3 {
    margin: 0 0 1rem 0;
    color: var(--text);
}

.empty-state p {
    margin: 0 0 2rem 0;
    font-size: 1.1rem;
}

/* Modern Modal */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1001;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.modal-overlay.show {
    opacity: 1;
    visibility: visible;
}

.modal-container {
    background: var(--surface);
    border-radius: 20px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    transform: scale(0.9) translateY(20px);
    transition: all 0.3s ease;
}

.modal-container.assign-modal {
    max-width: 700px;
    max-height: 95vh;
}

.modal-container.details {
    max-width: 80vw;
    max-height: 95vh;
}

.modal-overlay.show .modal-container {
    transform: scale(1) translateY(0);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem 2rem;
    border-bottom: 1px solid var(--border);
    background: var(--surface-soft);
}
.modal-header.details {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 2rem;
    border-bottom: 1px solid var(--border);
    background: var(--surface-soft);
}

.header-title-section h3 {
    margin: 0 0 0.25rem 0;
    font-weight: 600;
    color: var(--text);
    display: flex;
    align-items: center;
}

.header-subtitle {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.modal-close {
    width: 40px;
    height: 40px;
    border: none;
    background: var(--surface);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--text-muted);
}

.modal-close:hover {
    background: var(--danger);
    color: white;
}

.modal-body {
    padding: 1rem 2rem;
    max-height: calc(95vh - 200px);
    overflow-y: auto;
}

.modal-body.details {
    position: relative;

    padding: 0rem 0rem;
}
.modern-table thead th {
    position: sticky !important;
    top: -1rem;
    z-index: 100;
}

.form-group {
    margin-bottom: 0.1rem;
}

.form-group label,
.form-label {
    display: block;
    margin-bottom: 0.1rem;
    font-weight: 600;
    color: var(--text);
}

.form-group p {
    margin: 0;
    padding: 0.4rem 0;
    color: var(--text-muted);
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border);
    border-radius: 10px;
    background: var(--surface);
    color: var(--text);
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.shift-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.shift-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border: 2px solid var(--border);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.shift-option:hover {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.05);
}

.shift-option.selected {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.1);
}

.shift-badge {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
}

.shift-badge.large {
    padding: 0.75rem 1.25rem;
    font-size: 1rem;
}

.shift-details {
    text-align: right;
}

.modal-footer {
    padding: 1rem 2rem;
    gap: 0.5rem;
    border-top: 1px solid var(--border);
    background: var(--surface-soft);
}

.footer-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 1rem;
}

/* NEW: Assign Modal Specific Styles */

/* Step Indicator */
.step-indicator {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border);
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-muted);
    transition: all 0.3s ease;
}

.step.active {
    color: var(--primary);
}

.step.completed {
    color: var(--success);
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--surface-soft);
    border: 2px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.step.completed .step-number {
    background: var(--success);
    border-color: var(--success);
    color: white;
}

.step-label {
    font-size: 0.85rem;
    font-weight: 500;
    text-align: center;
}

.step-line {
    width: 60px;
    height: 2px;
    background: var(--border);
    transition: all 0.3s ease;
}

.step-line.active {
    background: var(--primary);
}

/* Step Content */
.step-content {
    animation: fadeIn 0.5s ease-in-out;
}

.form-section {
    margin-bottom: 2rem;
}

.section-title {
    display: flex;
    align-items: center;
    margin-bottom: 1.5rem;
    color: var(--text);
    font-size: 1.1rem;
    font-weight: 600;
    border-bottom: 1px solid var(--border);
    padding-bottom: 0.75rem;
}
.form-floating {
    position: absolute;
    top: -0.7rem;
    background: white;
    left: 1.5rem;
}

/* Employee Selection */
.assign-search {
    margin-bottom: 1.5rem;
}

.employee-selection-list {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid var(--border);
    border-radius: 12px;
    margin-bottom: 1rem;
}

.employee-selection-item {
    display: flex;
    flex-direction: column;
    align-items: start;
    gap: 1rem;
    /* padding: 1rem; */
    border-bottom: 1px solid var(--border);
    cursor: pointer;
    transition: all 0.3s ease;
}
.disabled-card {
    cursor: not-allowed;
    pointer-events: none;
    opacity: 0.5;
}

.employee-selection-card {
    display: flex;
    width: 100%;
    align-items: center;
    gap: 0.2rem;
    padding: 0.3rem;
    border-bottom: 1px solid var(--border);
    cursor: pointer;
    transition: all 0.3s ease;
}

.employee-selection-item:last-child {
    border-bottom: none;
}

.employee-selection-item:hover {
    background: var(--surface-soft);
}

.employee-selection-item.selected {
    background: rgba(99, 102, 241, 0.1);
    border-color: var(--primary);
}

.employee-checkbox {
    width: 18px;
    height: 18px;
    border: 2px solid var(--border);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-weight: bold;
    transition: all 0.3s ease;
}

.employee-selection-item.selected .employee-checkbox {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.employee-department {
    font-size: 0.8rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}

.selection-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.selected-count {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    background: rgba(99, 102, 241, 0.1);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 10px;
    color: var(--primary);
    font-weight: 500;
    font-size: 0.9rem;
}

/* Shift Type Selection */
.shift-type-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.shift-type-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border: 2px solid var(--border);
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: var(--surface);
}

.shift-type-card:hover {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.05);
}

.shift-type-card.selected {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.1);
}

.shift-type-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    background: var(--surface-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--primary);
}

.shift-type-content h5 {
    margin: 0 0 0.25rem 0;
    font-weight: 600;
    color: var(--text);
}

.shift-type-content p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--text-muted);
}

.shift-type-check {
    margin-left: auto;
    font-size: 1.25rem;
    color: var(--primary);
}

/* Date Selection */
.date-selection {
    margin-top: 1.5rem;
    padding: 0;
    /* box-shadow: var(--shadow-inset); */
}

.date-picker-grid {
    width: 100%;
    display: flex;
    justify-content: center;
    padding: 1rem 0rem;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.date-picker-item {
    width: 6rem;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.15rem;
    position: relative;
    cursor: pointer;
    border-radius: 12px;
    transition: all 0.3s ease;
    border: 2px solid var(--border);
}

.date-picker-item:hover {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.05);
}

.date-picker-item.selected {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.1);
}

.date-picker-item.today {
    border-color: var(--warning);
    background: rgba(245, 158, 11, 0.1);
}

.date-day {
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--text-muted);
}

.date-number {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text);
}

.date-check {
    position: absolute;
    top: -8px;
    right: -8px;
    color: var(--primary);
    font-size: 1.2rem;
}

.date-range-inputs {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.date-input-group {
    flex: 1;
}

.date-input-group label {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.date-input {
    width: 100%;
}

/* Assignment Summary */
.assignment-summary {
    background: var(--surface-soft);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.summary-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border);
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-label {
    display: flex;
    align-items: center;
    font-weight: 500;
    color: var(--text);
}

.summary-value {
    font-weight: 600;
    color: var(--primary);
}

/* Assign Shift Options */
.assign-shift-options {
    gap: 1rem;
}

.assign-shift-option {
    padding: 1.5rem;
}

.shift-description {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}
/* button */
.btn-modern {
    display: flex;

    align-items: center;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}

.btn-modern::before {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.2),
        transparent
    );
    transition: left 0.5s;
}

.btn-modern:hover::before {
    left: 100%;
}

.btn-primary {
    background-color: var(--primary);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(20px);
}

.btn-primary:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
    color: var(--text);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.btn-secondary {
    background: var(--surface);
    color: var(--text);
    border: 1px solid var(--border);
}

.btn-secondary:hover {
    background: var(--surface-soft);
    transform: translateY(-1px);
    box-shadow: var(--shadow);
}

.btn-success {
    background: var(--success);
    color: white;
}

.btn-success:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: var(--shadow);
}

.btn-link {
    background: none;
    border: none;
    color: var(--primary);
    padding: 0.5rem;
    cursor: pointer;
    font-size: 0.9rem;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-link:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

.shift-radio {
    font-size: 1.25rem;
    color: var(--primary);
}

/* Additional Options */
.additional-options {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}

.option-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.option-checkbox {
    width: 20px;
    height: 20px;
    margin: 0;
    accent-color: var(--primary);
}

.option-label {
    display: flex;
    align-items: center;
    font-weight: 500;
    color: var(--text);
    cursor: pointer;
    margin: 0;
}
.shadow-unix2 {
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

.option-description {
    display: block;
    color: var(--text-muted);
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

/* Animations
/* Animations */
@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05);
    }
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

@keyframes bounce {
    0%,
    80%,
    100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideIn {
    from {
        transform: translateX(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes shake {
    0%,
    100% {
        transform: translateX(0);
    }
    10%,
    30%,
    50%,
    70%,
    90% {
        transform: translateX(-5px);
    }
    20%,
    40%,
    60%,
    80% {
        transform: translateX(5px);
    }
}

@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
    }
}

@keyframes ripple {
    0% {
        transform: scale(0);
        opacity: 1;
    }
    100% {
        transform: scale(4);
        opacity: 0;
    }
}

/* Notification System */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    color: white;
    font-weight: 500;
    box-shadow: var(--shadow-lg);
    z-index: 10000;
    animation: slideIn 0.3s ease-out;
    min-width: 300px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.icon-container {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.notification.success {
    background: var(--gradient-success);
}

.notification.error {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.notification.warning {
    background: var(--gradient-warning);
}

.notification.info {
    background: var(--gradient-info);
}

.notification-icon {
    font-size: 1.25rem;
}

.notification-content {
    flex: 1;
}

.notification-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.notification-close:hover {
    opacity: 1;
}

/* Tooltip */
.tooltip {
    position: relative;
    cursor: help;
}

.tooltip::before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
    background: var(--dark);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1000;
}

.tooltip::after {
    content: "";
    position: absolute;
    bottom: 115%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: var(--dark);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.tooltip:hover::before,
.tooltip:hover::after {
    opacity: 1;
    visibility: visible;
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: var(--surface-soft);
    border-radius: 0px;
}

::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 0px;
    transition: background 0.3s ease;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}

::-webkit-scrollbar-corner {
    background: var(--surface-soft);
}

/* Focus Indicators */
*:focus {
    outline: none;
}

.focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

/* Selection */
::selection {
    background: rgba(99, 102, 241, 0.2);
    color: var(--text);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .modern-header {
        min-height: 120px;
    }

    .header-content {
        padding: 2rem;
    }

    .header-actions {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-end;
    }

    .date-range {
        padding: 0.75rem 1rem;
    }
}

@media (max-width: 992px) {
    .header-info {
        /* flex-direction: column; */
        /* text-align: center; */
        width: 100%;
        padding-bottom: 0.6rem;
        gap: 1rem;
    }

    .icon-container {
        width: 80px;
        height: 80px;
        padding: 1rem;
    }

    .title {
        font-size: 1.5rem;
    }

    .subtitle {
        font-size: 1rem;
    }

    .nav-controls {
        flex-direction: column;
        gap: 1rem;
    }

    .btn-nav {
        width: 100%;
        max-width: 200px;
    }

    /* .search-filter-section {
        flex-direction: column;
        flex: 1;
        align-items: stretch;
    } */

    .filters-container {
        flex-wrap: wrap;
    }

    .filter-dropdown {
        flex: 1;
        flex-grow: 1;
        min-width: 150px;
    }

    .cards-grid {
        grid-template-columns: 1fr;
    }

    .shift-type-options {
        grid-template-columns: 1fr;
    }

    .date-range-inputs {
        flex-direction: column;
    }

    .date-separator {
        display: none;
    }
}

@media (max-width: 768px) {
    .modern-header {
        min-height: 100px;
    }

    .header-content {
        padding: 1.5rem;
    }

    .header-info {
        gap: 0.75rem;
    }

    .icon-container {
        width: 50px;
        height: 50px;
    }

    .icon-container i {
        font-size: 1.5rem;
    }

    .title {
        font-size: 1.25rem;
    }

    .subtitle {
        font-size: 0.9rem;
    }

    .header-actions {
        flex-direction: row;
        justify-content: center;
        flex-wrap: wrap;
    }

    .date-range {
        padding: 0.5rem 0.75rem;
        font-size: 0.8rem;
    }

    .btn-modern {
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
    }

    .control-panel {
        padding: 1rem;
    }

    .modern-table {
        font-size: 0.8rem;
    }

    .employee-avatar {
        width: 35px;
        height: 35px;
        font-size: 0.75rem;
    }

    .shift-container {
        padding: 0.5rem;
    }

    .shift-name {
        font-size: 0.75rem;
    }

    .shift-time {
        font-size: 0.7rem;
    }

    .modal-container {
        width: 95%;
        margin: 1rem;
    }

    .modal-header {
        padding: 1rem;
    }
    .modal-header.details {
        padding: 0.6rem;
    }

    .modal-body {
        position: relative;

        padding: 1rem;
    }

    .modal-footer {
        padding: 1rem;
    }

    .footer-actions {
        flex-direction: column;
        gap: 0.75rem;
    }

    .footer-actions .btn-modern {
        width: 100%;
        justify-content: center;
    }

    .step-indicator {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
    }

    .step-number {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }

    .step-label {
        font-size: 0.75rem;
    }

    .step-line {
        width: 40px;
    }

    .date-picker-grid {
        grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        gap: 0.5rem;
    }

    .date-picker-item {
        padding: 0.75rem;
    }

    .employee-selection-item {
        padding: 0.75rem;
    }

    .employee-avatar.large {
        width: 50px;
        height: 50px;
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .header-content {
        padding: 1rem;
    }
    .button-header {
        margin-top: 0.7rem;
        font-size: 0.8rem !important;
    }

    .title-header {
        font-size: 1.1rem !important;
    }

    .sub-title-header {
        font-size: 0.8rem !important;
    }

    .subtitle {
        font-size: 0.85rem;
    }

    .control-panel {
        padding: 0.75rem;
    }

    .nav-controls {
        margin-bottom: 1rem;
        padding-bottom: 1rem;
    }

    .search-filter-section {
        gap: 0.75rem;
    }
    .search-container {
        display: flex;
        flex-grow: 1;
        flex: 1;
    }

    .table-wrapper {
        border-radius: 12px;
        overflow-x: auto;
    }

    .sticky-column {
        min-width: 100px;
        position: relative;
        left: auto;
        padding: 0.5rem 0.5rem;
        box-shadow: none;
    }

    .day-header {
        min-width: 120px;
    }

    .employee-info {
        gap: 0.75rem;
    }

    .employee-details {
        gap: 0.15rem;
        padding: 0.5rem 0.5rem;
    }

    .employee-name {
        font-size: 0.9rem;
    }

    .employee-id {
        font-size: 0.75rem;
    }

    .shift-cell {
        padding: 0.2rem;
    }
    .table-wrapper thead th {
        padding: 0.5rem;
    }

    .shift-container {
        padding: 0.5rem;
        border-radius: 8px;
    }

    .pagination-container {
        padding: 1rem;
    }

    .card-view {
        padding: 1rem;
    }

    .employee-card {
        padding: 1rem;
    }

    .card-header {
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
    }

    .modal-container.assign-modal {
        width: 98%;
        max-height: 98vh;
    }

    .assignment-summary {
        padding: 1rem;
    }

    .summary-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
        padding: 0.5rem 0;
    }

    .shift-type-card {
        padding: 1rem;
        flex-direction: column;
        text-align: center;
        gap: 0.75rem;
    }

    .shift-type-icon {
        width: 40px;
        height: 40px;
        font-size: 1.25rem;
    }

    .option-item {
        align-items: flex-start;
        gap: 0.75rem;
    }

    .option-label {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}

/* Print Styles */
@media print {
    .control-panel,
    .modal-overlay,
    .btn-modern,
    .edit-icon,
    .pagination-container {
        display: none !important;
    }

    .modern-header {
        background: white;
        /* color: black !important; */
        box-shadow: none !important;
    }

    .header-background {
        display: none;
    }

    .modern-table {
        border-collapse: collapse;
    }

    .modern-table th,
    .modern-table td {
        border: 1px solid #000 !important;
        padding: 0.5rem !important;
    }

    .shift-container {
        background: transparent !important;
        border: 1px solid #000 !important;
    }

    body {
        background: white !important;
        color: black !important;
    }
}

/* Utility Classes */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.text-center {
    text-align: center;
}

.text-left {
    text-align: left;
}

.text-right {
    text-align: right;
}

.d-flex {
    display: flex;
}

.d-grid {
    display: grid;
}

.d-none {
    display: none;
}

.align-items-center {
    align-items: center;
}

.align-items-start {
    align-items: flex-start;
}

.justify-content-center {
    justify-content: center;
}

.justify-content-between {
    justify-content: space-between;
}

.justify-content-end {
    justify-content: flex-end;
}

.flex-wrap {
    flex-wrap: wrap;
}

.gap-1 {
    gap: 0.25rem;
}

.gap-2 {
    gap: 0.5rem;
}

.gap-3 {
    gap: 1rem;
}

.gap-4 {
    gap: 1.5rem;
}

.m-0 {
    margin: 0;
}

.mb-1 {
    margin-bottom: 0.25rem;
}

.mb-2 {
    margin-bottom: 0.5rem;
}

.mb-3 {
    margin-bottom: 1rem;
}

.mb-4 {
    margin-bottom: 1.5rem;
}

.p-0 {
    padding: 0;
}

.p-1 {
    padding: 0.25rem;
}

.p-2 {
    padding: 0.5rem;
}

.p-3 {
    padding: 1rem;
}

.w-100 {
    width: 100%;
}

.h-100 {
    height: 100%;
}

.position-relative {
    position: relative;
}

.position-absolute {
    position: absolute;
}

.rounded {
    border-radius: 0.5rem;
}

.rounded-lg {
    border-radius: 1rem;
}

.shadow {
    box-shadow: var(--shadow);
}

.shadow-lg {
    box-shadow: var(--shadow-lg);
}

.opacity-50 {
    opacity: 0.5;
}

.opacity-75 {
    opacity: 0.75;
}

.cursor-pointer {
    cursor: pointer;
}

.cursor-not-allowed {
    cursor: not-allowed;
}

.overflow-hidden {
    overflow: hidden;
}

.overflow-auto {
    overflow: auto;
}

.text-primary {
    color: var(--primary);
}

.text-secondary {
    color: var(--secondary);
}

.text-success {
    color: var(--success);
}

.text-warning {
    color: var(--warning);
}

.text-danger {
    color: var(--danger);
}

.text-muted {
    color: var(--text-muted);
}

.bg-primary {
    background-color: var(--primary);
}

.bg-secondary {
    background-color: var(--secondary);
}

.bg-success {
    background-color: var(--success);
}

.bg-warning {
    background-color: var(--warning);
}

.bg-danger {
    background-color: var(--danger);
}

.bg-surface {
    background-color: var(--surface);
}

.bg-surface-soft {
    background-color: var(--surface-soft);
}

.opa-100 {
    opacity: 100%;
}

.opa-0 {
    opacity: 0%;
}

/* Component Specific Utilities */
.fade-in {
    animation: fadeIn 0.5s ease-in-out;
}

.slide-in {
    animation: slideIn 0.5s ease-out;
}

.slide-up {
    animation: slideUp 0.5s ease-out;
}

.pulse {
    animation: pulse 2s ease-in-out infinite;
}

.spin {
    animation: spin 1s linear infinite;
}

.bounce {
    animation: bounce 1.4s ease-in-out infinite;
}

.shake {
    animation: shake 0.82s cubic-bezier(0.36, 0.07, 0.19, 0.97);
}

.zoom-in {
    animation: zoomIn 0.5s ease-out;
}

/* Interactive States */
.hover-lift:hover {
    transform: translateY(-2px);
    transition: transform 0.3s ease;
}

.hover-scale:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}

.hover-shadow:hover {
    box-shadow: var(--shadow-lg);
    transition: box-shadow 0.3s ease;
}

/* Loading States */
.loading-state {
    position: relative;
    overflow: hidden;
}

.loading-state::after {
    content: "";
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.4),
        transparent
    );
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% {
        left: -100%;
    }
    100% {
        left: 100%;
    }
}

.skeleton {
    background: linear-gradient(
        90deg,
        var(--surface-soft) 25%,
        var(--border) 50%,
        var(--surface-soft) 75%
    );
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

/* Error States */
.error-shake {
    animation: shake 0.82s cubic-bezier(0.36, 0.07, 0.19, 0.97);
}

.error-border {
    border-color: var(--danger) !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
}
</style>

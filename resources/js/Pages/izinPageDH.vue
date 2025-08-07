<template>
    <!-- Header -->
    <div
        class="row d-flex align-items-center justify-content-center mb-3 fade-in"
    >
        <div class="col-md-11">
            <div class="modern-header">
                <div class="header-background"></div>
                <div class="header-content">
                    <div class="row align-items-center">
                        <div class="col-sm-8">
                            <div class="header-info">
                                <div
                                    @click="log()"
                                    class="icon-container d-flex align-items-center justify-content-center"
                                >
                                    <i
                                        class="bi bi-person-badge d-flex justify-content-center align-items-center"
                                    ></i>
                                </div>
                                <div class="text-content">
                                    <h2
                                        class="title fw-bold text-white text-start"
                                    >
                                        Halo,
                                        {{
                                            capitalize(
                                                namaKaryawan
                                                    ?.split(" ")
                                                    .slice(0, 2)
                                                    .join(" ")
                                            )
                                        }}
                                    </h2>
                                    <p class="subtitle">
                                        Manage Pengajuan Izin
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div
                                class="header-actions d-flex gap-2 flex-nowrap flex-sm-column justify-content-center justify-content-md-start justify-content-md-center align-items-md-end"
                            >
                                <div
                                    class="date-range btn-modern px-2 px-md-3 py-1 gap-2 gap-md-3"
                                >
                                    <div class="date-item">
                                        <i
                                            v-if="dayNight == 'Siang'"
                                            class="bi bi-cloud-sun-fill me-2 fs-4 fade-in"
                                        ></i>
                                        <i
                                            v-else
                                            class="bi bi-cloud-moon-fill me-2 fs-4 fade-in"
                                        ></i>
                                        <span class="fs-md-6 text-header">{{
                                            formatDateString(currentDate)
                                        }}</span>
                                    </div>
                                </div>
                                <!-- <button
                                    class="btn-modern btn-primary flex-nowrap w-full px-md-2 py-md-2"
                                    @click="showAssignModal = true"
                                >
                                    <i
                                        class="bi bi-plus-circle me-2 d-flex justify-content-center align-items-center"
                                    ></i>
                                    <span class="text-header">Ajukan Izin</span>
                                </button> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Control Panel -->
    <div class="row d-flex justify-content-center mb-1 mb-md-3 fade-in">
        <div class="col-md-11">
            <div class="control-panel">
                <div class="search-filter-section">
                    <div
                        class="d-md-flex flex-grow-1 row gap-0 gap-md-2 px-2 px-md-0"
                    >
                        <div
                            class="d-md-none d-block flex-grow-1 col-6 col-md-12 p-1"
                        >
                            <input
                                v-if="!selectedDateFilter"
                                v-model="selectedDateFilter"
                                type="date"
                                class="form-control"
                                placeholder="Semua"
                                style="cursor: pointer"
                                title="Pilih Tanggal"
                            />
                            <div
                                v-else
                                type="date"
                                class="form-control"
                                placeholder="Semua"
                                title="Pilih Tanggal"
                                style="cursor: pointer"
                            >
                                {{ formatDateToDMY(selectedDateFilter) }}
                                <span
                                    @click="selectedDateFilter = null"
                                    v-tooltip="'Hapus filter'"
                                    class="closeFilter p-1 rounded-3 ms-2"
                                    ><i class="bi bi-x"></i
                                ></span>
                            </div>
                        </div>
                        <div
                            class="d-md-none d-block flex-grow-1 col-6 col-md-12 p-1 position-relative"
                        >
                            <select
                                v-model="filterDropdown"
                                name=""
                                id=""
                                class="form-control"
                            >
                                <option selected value="">Semua</option>
                                <option value="Izin Pribadi">Izin</option>
                                <option value="cuti">Cuti</option>
                                <option value="sakit">Sakit</option>
                                <option value="pulang cepat">
                                    Pulang cepat
                                </option>
                                <option value="terlambat">Terlambat</option>
                            </select>
                            <i
                                v-if="filterDropdown == ''"
                                class="bi bi-chevron-down position-absolute"
                                style="right: 17px; top: 18px"
                            ></i>
                            <span
                                v-else
                                style="right: 17px; top: 13px"
                                @click="filterDropdown = ''"
                                class="closeFilter p-1 rounded-3 ms-2 position-absolute"
                                ><i class="bi bi-x"></i
                            ></span>
                        </div>
                        <div class="search-container d-md-flex d-none pe-0">
                            <i
                                class="ms-2 bi bi-search search-icon d-flex justify-content-center align-items-center"
                            ></i>
                            <input
                                type="text"
                                class="search-input"
                                placeholder="Cari izin..."
                                v-model="searchQuery"
                                @focus="showSearchSuggestions = true"
                                @blur="hideSuggestions"
                            />
                            <div
                                class="search-results-count"
                                v-if="searchQuery"
                            >
                                {{ filteredPermissions.length }} hasil
                            </div>
                            <div
                                class="search-suggestions"
                                v-if="showSearchSuggestions"
                            >
                                <div
                                    class="suggestion-item"
                                    v-for="suggestion in filteredSuggestions"
                                    :key="suggestion"
                                    @mousedown="selectSuggestion(suggestion)"
                                >
                                    <i class="bi bi-clock-history"></i>
                                    {{ suggestion }}
                                </div>
                                <div
                                    v-if="
                                        searchQuery &&
                                        filteredSuggestions.length === 0
                                    "
                                    class="suggestion-item text-muted"
                                >
                                    Tidak ada saran ditemukan.
                                </div>
                            </div>
                        </div>
                        <div
                            style="width: 10rem"
                            class="d-md-block w-max d-none position-relative ps-0"
                        >
                            <select
                                v-model="filterDropdown"
                                name=""
                                id=""
                                class="form-control w-max"
                            >
                                <option selected value="">Semua</option>
                                <option value="Izin Pribadi">Izin</option>
                                <option value="cuti">Cuti</option>
                                <option value="sakit">Sakit</option>
                                <option value="pulang cepat">
                                    Pulang cepat
                                </option>
                                <option value="terlambat">Terlambat</option>
                            </select>
                            <i
                                v-if="filterDropdown == ''"
                                class="bi bi-chevron-down position-absolute"
                                style="right: 25px; top: 14px"
                            ></i>
                            <span
                                v-else
                                style="right: 20px; top: 8px"
                                @click="filterDropdown = ''"
                                class="closeFilter p-1 rounded-3 ms-2 position-absolute"
                                ><i class="bi bi-x"></i
                            ></span>
                        </div>
                    </div>

                    <div class="filters-container flex-grow-1 flex-md-grow-0">
                        <div
                            class="search-container flex-grow-1 d-block d-md-none"
                        >
                            <i
                                class="bi bi-search search-icon d-flex justify-content-center align-items-center"
                            ></i>
                            <input
                                type="text"
                                class="search-input"
                                placeholder="Cari izin..."
                                v-model="searchQuery"
                                @focus="showSearchSuggestions = true"
                                @blur="hideSuggestions"
                            />
                            <div
                                class="search-results-count"
                                v-if="searchQuery"
                            >
                                {{ filteredPermissions.length }} hasil
                            </div>
                            <div
                                class="search-suggestions"
                                v-if="showSearchSuggestions"
                            >
                                <div
                                    class="suggestion-item"
                                    v-for="suggestion in filteredSuggestions"
                                    :key="suggestion"
                                    @mousedown="selectSuggestion(suggestion)"
                                >
                                    <i class="bi bi-clock-history"></i>
                                    {{ suggestion }}
                                </div>
                                <div
                                    v-if="
                                        searchQuery &&
                                        filteredSuggestions.length === 0
                                    "
                                    class="suggestion-item text-muted"
                                >
                                    Tidak ada saran ditemukan.
                                </div>
                            </div>
                        </div>

                        <div class="flex-grow-1 d-md-block d-none">
                            <input
                                v-if="!selectedDateFilter"
                                v-model="selectedDateFilter"
                                type="date"
                                class="form-control"
                                placeholder="Semua"
                                style="cursor: pointer"
                                title="Pilih Tanggal"
                            />
                            <div
                                v-else
                                type="date"
                                class="form-control"
                                placeholder="Semua"
                                title="Pilih Tanggal"
                                style="cursor: pointer"
                            >
                                {{ formatDateToDMY(selectedDateFilter) }}
                                <span
                                    @click="selectedDateFilter = null"
                                    v-tooltip="'Hapus filter'"
                                    class="closeFilter p-1 rounded-3 ms-2"
                                    ><i class="bi bi-x"></i
                                ></span>
                            </div>
                        </div>
                        <!-- view Toggle -->
                        <div class="view-toggle py-2">
                            <button
                                class="toggle-btn"
                                :class="{ active: viewMode === 'table' }"
                                @click="setViewMode('table')"
                                title="Tampilan Tabel"
                            >
                                <i
                                    class="bi bi-table d-flex justify-content-center align-items-center"
                                ></i>
                            </button>
                            <button
                                class="toggle-btn"
                                :class="{ active: viewMode === 'card' }"
                                @click="setViewMode('card')"
                                title="Tampilan Kartu"
                            >
                                <i
                                    class="bi bi-grid-3x3-gap d-flex justify-content-center align-items-center"
                                ></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row d-flex justify-content-center fade-in">
        <div class="col-md-11">
            <div class="content-container">
                <!-- Loading -->
                <div v-if="loading" class="loading-container">
                    <div class="loading-animation">
                        <div class="loading-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <p class="loading-text">Memuat data izin...</p>
                </div>

                <!-- Tidak ada Isi -->
                <div
                    v-else-if="filteredPermissions.length === 0 && !loading"
                    class="empty-state"
                >
                    <div class="empty-icon">
                        <i
                            class="bi bi-inbox d-flex justify-content-center align-items-center"
                        ></i>
                    </div>
                    <h4>Tidak Ada Data Pengajuan Izin</h4>
                    <p>Anda belum memiliki pengajuan izin dari team.</p>
                    <!-- <button
                        class="btn-modern btn-primary"
                        @click="showAssignModal = true"
                    >
                        <i
                            class="bi bi-plus-circle me-2 d-flex justify-content-center align-items-center"
                        ></i>
                        Ajukan Izin
                    </button> -->
                </div>

                <transition name="fade-switch" mode="out-in">
                    <div
                        v-if="
                            viewMode === 'table' &&
                            !loading &&
                            filteredPermissions.length > 0
                        "
                        class="table-view-container"
                        key="table"
                    >
                        <div class="table-responsive-wrapper">
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th class="header-cell sticky-column">
                                            <div class="header-cell-content">
                                                <i
                                                    class="bi bi-stack me-2 d-flex justify-content-center align-items-center"
                                                ></i>
                                                <span
                                                    >No Transaksi
                                                    <small class="fw-light"
                                                        >({{
                                                            filteredPermissions.length
                                                        }})</small
                                                    ></span
                                                >
                                            </div>
                                        </th>
                                        <th class="header-cell">
                                            <div class="header-cell-content">
                                                <i
                                                    class="bi bi-people me-2 d-flex justify-content-center align-items-center"
                                                ></i>
                                                <span>Nama Pengaju</span>
                                            </div>
                                        </th>
                                        <th class="header-cell">
                                            <div class="header-cell-content">
                                                <i
                                                    class="bi bi-calendar me-2 d-flex justify-content-center align-items-center"
                                                ></i>
                                                <span>Tanggal</span>
                                            </div>
                                        </th>

                                        <th class="header-cell">
                                            <div class="header-cell-content">
                                                <i
                                                    class="bi bi-chat-square-text me-2 d-flex justify-content-center align-items-center"
                                                ></i>
                                                <span>Alasan</span>
                                            </div>
                                        </th>
                                        <th class="header-cell">
                                            <div class="header-cell-content">
                                                <i
                                                    class="bi bi-paperclip me-2 d-flex justify-content-center align-items-center"
                                                ></i>
                                                <span>Lampiran</span>
                                            </div>
                                        </th>
                                        <th class="header-cell">
                                            <div class="header-cell-content">
                                                <i
                                                    class="bi bi-paperclip me-2 d-flex justify-content-center align-items-center"
                                                ></i>
                                                <span>Step</span>
                                            </div>
                                        </th>

                                        <th class="header-cell">
                                            <div class="header-cell-content">
                                                <i
                                                    class="bi bi-check-circle me-2 d-flex justify-content-center align-items-center"
                                                ></i>
                                                <span>Status</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="item in paginatedPermissions"
                                        :key="item.No_Transaksi"
                                        class="table-row"
                                        @click="showDetail(item)"
                                    >
                                        <td class="sticky-column">
                                            <div class="transaction-cell">
                                                <div class="transaction-id">
                                                    {{ item.No_Transaksi }}
                                                </div>
                                                <div
                                                    class="transaction-type"
                                                    :class="
                                                        getTypeClass(
                                                            item.Tipe_Izin
                                                        )
                                                    "
                                                >
                                                    <span
                                                        class="type-indicator"
                                                    ></span>
                                                    {{
                                                        parseIzin(
                                                            item.Tipe_Izin
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="date-table-cell">
                                                {{
                                                    item.requester_Nama
                                                        ?.split(" ")
                                                        .slice(0, 2)
                                                        .join(" ")
                                                }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="date-table-cell">
                                                {{ item.TanggalIzin }}
                                            </div>
                                        </td>
                                        <td>
                                            <div
                                                class="reason-cell"
                                                :title="item.Alasan"
                                            >
                                                {{
                                                    truncateText(
                                                        item.Alasan,
                                                        30
                                                    )
                                                }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="attachment-cell">
                                                <a
                                                    v-if="item.Lampiran"
                                                    href="#"
                                                    @click.prevent.stop="
                                                        viewAttachment(
                                                            item.Lampiran
                                                        )
                                                    "
                                                    target="_blank"
                                                    class="attachment-link"
                                                    :class="{
                                                        'is-loading':
                                                            loadingAttachment,
                                                    }"
                                                    :disabled="
                                                        loadingAttachment
                                                    "
                                                >
                                                    <span
                                                        class="d-flex justify-content-center align-items-center"
                                                        v-if="
                                                            !loadingAttachment
                                                        "
                                                    >
                                                        <i
                                                            class="bi me-1 bi-file-earmark-arrow-down d-flex justify-content-center align-items-center"
                                                        ></i>
                                                        Lihat
                                                    </span>
                                                    <span v-else>
                                                        <i
                                                            class="bi bi-arrow-clockwise animated-spin"
                                                        ></i>
                                                        Memuat...
                                                    </span>
                                                </a>
                                                <span
                                                    v-else
                                                    class="no-attachment"
                                                >
                                                    <i class="bi bi-file-x"></i>
                                                    Tidak ada
                                                </span>
                                            </div>
                                        </td>
                                        <td
                                            @click.stop
                                            @mouseover="
                                                approvalData = [item.STATUSLIST]
                                            "
                                        >
                                            <el-tooltip
                                                :content="
                                                    item.status == 'Ditolak' &&
                                                    !item.Status_Approval_Saat_Ini
                                                        ? ''
                                                        : tooltipContent
                                                "
                                                raw-content
                                                placement="top"
                                                :width="350"
                                            >
                                                <div class="status-cell">
                                                    {{
                                                        item.currentFlow +
                                                        "/" +
                                                        item.MaxFlow
                                                    }}
                                                    Step
                                                </div>
                                            </el-tooltip>
                                        </td>
                                        <td>
                                            <div class="status-cell">
                                                <span
                                                    v-if="
                                                        !item.Status_Approval_Saat_Ini &&
                                                        item.status ==
                                                            'Diproses'
                                                    "
                                                    class="status-badge p-0 d-flex justify-content-between"
                                                    style="width: 10rem"
                                                >
                                                    <span
                                                        @click.stop="
                                                            showConfirmYesModal(
                                                                'Terima',
                                                                item.No_Transaksi,
                                                                item.Requester_Kode_Karyawan,
                                                                item.Approver_Kode_Karyawan
                                                            )
                                                        "
                                                        class="d-flex px-2 justify-content-center text-success cursor-pointer buttonAccept right"
                                                        ><i
                                                            class="bi bi-check-circle me-1"
                                                        ></i
                                                        >Terima</span
                                                    >
                                                    <span
                                                        @click.stop="
                                                            showConfirmNoModal(
                                                                'Tolak',
                                                                item.No_Transaksi,
                                                                item.Requester_Kode_Karyawan,
                                                                item.Approver_Kode_Karyawan
                                                            )
                                                        "
                                                        class="d-flex px-2 justify-content-center text-danger cursor-pointer buttonAccept left"
                                                        ><i
                                                            class="bi bi-x-circle me-1"
                                                        ></i
                                                        >Tolak</span
                                                    >
                                                </span>
                                                <span
                                                    v-else-if="
                                                        item.Status_Approval_Saat_Ini
                                                            ? item.Status_Approval_Saat_Ini?.trim() ===
                                                              'Y'
                                                            : false
                                                    "
                                                    class="text-success"
                                                    >DISETUJUI</span
                                                >
                                                <span
                                                    v-else-if="
                                                        !item.Status_Approval_Saat_Ini &&
                                                        item.status == 'Ditolak'
                                                    "
                                                    class="text-danger"
                                                    >Ditolak Oleh Sistem</span
                                                >
                                                <span v-else class="text-danger"
                                                    >DITOLAK
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-container" v-if="totalPages > 1">
                            <button
                                class="page-btn"
                                @click="prevPage"
                                :disabled="currentPage === 1"
                            >
                                <i
                                    class="bi bi-chevron-left d-flex justify-content-center align-items-center"
                                ></i>
                            </button>
                            <div class="page-numbers">
                                <button
                                    v-for="page in visiblePages"
                                    :key="page"
                                    class="page-number"
                                    :class="{ active: page === currentPage }"
                                    @click="goToPage(page)"
                                >
                                    {{ page }}
                                </button>
                            </div>
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

                    <div
                        v-else-if="
                            viewMode === 'card' &&
                            !loading &&
                            filteredPermissions.length > 0
                        "
                        class="card-view-container"
                        key="card"
                    >
                        <div class="card-grid">
                            <div
                                v-for="item in paginatedPermissions"
                                :key="item.No_Transaksi"
                                class="permission-card"
                                @click="showDetail(item)"
                            >
                                <div
                                    class="card-header"
                                    :class="
                                        getStatusHeaderClass(
                                            item.Status_Approval_Saat_Ini
                                        )
                                    "
                                >
                                    <div class="card-date">
                                        {{ item.TanggalIzin }}
                                    </div>
                                    <div class="card-status">
                                        {{
                                            maknaStatus(
                                                item.Status_Approval_Saat_Ini
                                            )
                                        }}
                                    </div>
                                </div>
                                <div class="card-body row">
                                    <div class="col-md-8 col-6">
                                        <div class="card-row">
                                            <i
                                                class="bi bi-hash d-flex justify-content-center align-items-center"
                                            ></i>
                                            <div class="card-label">
                                                No. Transaksi:
                                            </div>
                                            <div class="card-value">
                                                {{ item.No_Transaksi }}
                                            </div>
                                        </div>
                                        <div class="card-row">
                                            <i
                                                class="bi bi-tag d-flex justify-content-center align-items-center"
                                            ></i>
                                            <div class="card-label">
                                                Jenis Izin:
                                            </div>
                                            <div class="card-value">
                                                {{ parseIzin(item.Tipe_Izin) }}
                                            </div>
                                        </div>
                                        <div class="card-row">
                                            <i
                                                class="bi bi-chat-dots d-flex justify-content-center align-items-center"
                                            ></i>
                                            <div class="card-label">
                                                Alasan:
                                            </div>
                                            <div class="card-value">
                                                {{
                                                    truncateText(
                                                        item.Alasan,
                                                        20
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div v-if="item.Waktu" class="card-row">
                                            <i
                                                class="bi bi-clock d-flex justify-content-center align-items-center"
                                            ></i>
                                            <div class="card-label">Waktu:</div>
                                            <div class="card-value">
                                                {{
                                                    formatSliderTooltip(
                                                        item.Waktu
                                                    )
                                                }}
                                            </div>
                                        </div>
                                        <div class="card-row">
                                            <i
                                                class="bi bi-paperclip d-flex justify-content-center align-items-center"
                                            ></i>
                                            <div class="card-label">
                                                Lampiran:
                                            </div>
                                            <div class="card-value">
                                                <a
                                                    v-if="item.Lampiran"
                                                    href="#"
                                                    @click.prevent.stop="
                                                        viewAttachment(
                                                            item.Lampiran
                                                        )
                                                    "
                                                    target="_blank"
                                                    class="card-link"
                                                    :class="{
                                                        'is-loading':
                                                            loadingAttachment,
                                                    }"
                                                    :disabled="
                                                        loadingAttachment
                                                    "
                                                >
                                                    <span
                                                        class="d-flex justify-content-center align-items-center"
                                                        v-if="
                                                            !loadingAttachment
                                                        "
                                                    >
                                                        Lihat File
                                                    </span>
                                                    <span v-else>
                                                        <i
                                                            class="bi bi-arrow-clockwise animated-spin"
                                                        ></i>
                                                        Memuat...
                                                    </span>
                                                </a>
                                                <span v-else class="no-file">
                                                    Tidak ada
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6 p-0">
                                        <div
                                            class="card-label flex-column mb-2"
                                        >
                                            Progress:
                                        </div>
                                        <div
                                            class="card-value"
                                            style="height: 50%"
                                        >
                                            <el-steps
                                                align-center
                                                style="
                                                    font-size: 0.5rem;
                                                    max-height: 15rem;
                                                "
                                                direction="vertical"
                                                :active="2"
                                            >
                                                <el-step
                                                    align-center
                                                    v-for="(
                                                        data, index
                                                    ) in parsedDataArray(
                                                        item.STATUSLIST
                                                    )"
                                                    :status="
                                                        data.status == 'Y'
                                                            ? 'success'
                                                            : data.status == 'T'
                                                            ? 'error'
                                                            : 'wait'
                                                    "
                                                    :key="index"
                                                    :title="data.name"
                                                />
                                            </el-steps>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <!-- <button
                                        class="card-btn delete-btn"
                                        @click.stop="
                                            showDeleteConfirm(item.No_Transaksi)
                                        "
                                    >
                                        <i
                                            class="bi bi-trash d-flex justify-content-center align-items-center"
                                        ></i>
                                        Hapus
                                    </button> -->
                                    <div class="card-btn px-0 py-0">
                                        <div class="status-cell">
                                            <span
                                                v-if="
                                                    !item.Status_Approval_Saat_Ini &&
                                                    item.status == 'Diproses'
                                                "
                                                class="d-flex justify-content-between"
                                                style="width: 10rem"
                                            >
                                                <span
                                                    style="
                                                        cursor: pointer !important;
                                                        border-radius: 8px 0px
                                                            0px 8px !important;
                                                    "
                                                    @click.stop="
                                                        showConfirmYesModal(
                                                            'Terima',
                                                            item.No_Transaksi,
                                                            item.Requester_Kode_Karyawan,
                                                            item.Approver_Kode_Karyawan
                                                        )
                                                    "
                                                    class="d-flex pe-1 ps-1 justify-content-center text-success cursor-pointer buttonAccept right"
                                                    ><i
                                                        class="bi bi-check-circle me-1"
                                                    ></i
                                                    >Terima</span
                                                >
                                                <span
                                                    style="
                                                        cursor: pointer !important;
                                                        border-radius: 0 8px 8px
                                                            0 !important;
                                                    "
                                                    @click.stop="
                                                        showConfirmNoModal(
                                                            'Tolak',
                                                            item.No_Transaksi,
                                                            item.Requester_Kode_Karyawan,
                                                            item.Approver_Kode_Karyawan
                                                        )
                                                    "
                                                    class="d-flex ps-1 pe-1 justify-content-center text-danger cursor-pointer buttonAccept left"
                                                    ><i
                                                        class="bi bi-x-circle me-1"
                                                    ></i
                                                    >Tolak</span
                                                >
                                            </span>
                                            <span
                                                v-else-if="
                                                    item.Status_Approval_Saat_Ini
                                                        ? item.Status_Approval_Saat_Ini?.trim() ===
                                                          'Y'
                                                        : false
                                                "
                                                class="text-success"
                                                >DISETUJUI</span
                                            >
                                            <span
                                                v-else-if="
                                                    !item.Status_Approval_Saat_Ini &&
                                                    item.status == 'Ditolak'
                                                "
                                                class="text-danger"
                                                >Ditolak Oleh Sistem</span
                                            >
                                            <span v-else class="text-danger"
                                                >DITOLAK</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pagination-container" v-if="totalPages > 1">
                            <button
                                class="page-btn"
                                @click="prevPage"
                                :disabled="currentPage === 1"
                            >
                                <i
                                    class="bi bi-chevron-left d-flex justify-content-center align-items-center"
                                ></i>
                            </button>
                            <div class="page-info">
                                Halaman {{ currentPage }} dari {{ totalPages }}
                            </div>
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
                </transition>
            </div>
        </div>
    </div>

    <!-- Detail Modal -->
    <el-dialog
        v-model="showDetailModal"
        :title="`Detail Izin - ${selectedItem?.No_Transaksi || ''}`"
        width="70%"
        :fullscreen="isMobile()"
        class="detail-modal"
        :lock-scroll="true"
    >
        <div v-if="selectedItem" class="detail-modal-content">
            <div class="detail-grid">
                <!-- Setiap detail row menggunakan grid system -->
                <div class="detail-item">
                    <span class="detail-label">Jenis Izin:</span>
                    <span class="detail-value highlight">
                        {{ parseIzin(selectedItem.Tipe_Izin) }}
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Tanggal:</span>
                    <span class="detail-value">
                        {{ selectedItem.TanggalIzin }}
                    </span>
                </div>

                <div v-if="selectedItem.Jam_Mulai" class="detail-item">
                    <span class="detail-label">Waktu:</span>
                    <span class="detail-value">
                        {{
                            selectedItem.Jam_Mulai?.split(":")
                                .slice(0, 2)
                                .join(":")
                        }}
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <el-tag
                            :type="
                                getStatusTagType(
                                    selectedItem.Status_Approval_Saat_Ini
                                )
                            "
                            size="medium"
                        >
                            <span
                                v-if="
                                    selectedItem.Status_Approval_Saat_Ini
                                        ? selectedItem.Status_Approval_Saat_Ini?.trim() ===
                                          'Y'
                                        : false
                                "
                                class="text-success"
                                >DISETUJUI</span
                            >
                            <span
                                v-else-if="
                                    selectedItem.Status_Approval_Saat_Ini?.trim() ===
                                        'T' && selectedItem.status == 'Ditolak'
                                "
                                class="text-danger"
                                >DITOLAK</span
                            >
                            <span
                                v-else-if="
                                    !selectedItem.Status_Approval_Saat_Ini &&
                                    selectedItem.status == 'Ditolak'
                                "
                                class="text-danger"
                                >Ditolak Oleh Sistem</span
                            >
                            <span v-else class="text-danger"
                                >Menunggu Keputusan</span
                            >
                        </el-tag>
                    </span>
                </div>

                <div class="detail-item full-width">
                    <span class="detail-label">Alasan:</span>
                    <span class="detail-value multiline">
                        {{ selectedItem.Alasan }}
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Lampiran:</span>
                    <span class="detail-value">
                        <el-button
                            v-if="selectedItem.Lampiran"
                            type="text"
                            :loading="loadingAttachment"
                            @click="viewAttachment(selectedItem.Lampiran)"
                            icon="el-icon-paperclip"
                        >
                            Lihat Lampiran
                        </el-button>
                        <span v-else class="empty-value">-</span>
                    </span>
                </div>

                <div class="detail-item full-width">
                    <div class="approval-progress">
                        <div class="progress-title">Proses Persetujuan:</div>
                        <el-steps
                            :active="getActiveStep(selectedItem.STATUSLIST)"
                            align-center
                        >
                            <el-step
                                v-for="(data, index) in parsedDataArray(
                                    selectedItem.STATUSLIST
                                )"
                                :key="index"
                                :title="data.name"
                                :status="getStepStatus(data.status)"
                            />
                        </el-steps>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <div
                v-if="selectedItem.status == 'Diproses'"
                class="modal-footer-actions p-0 pt-3"
            >
                <button
                    class="btn-modern btn-danger"
                    @click="
                        showConfirmNoModal(
                            'Tolak',
                            selectedItem.No_Transaksi,
                            selectedItem.Requester_Kode_Karyawan,
                            selectedItem.Approver_Kode_Karyawan
                        )
                    "
                >
                    <i
                        class="bi bi-x fs-4 d-flex justify-content-center align-items-center me-1"
                    ></i>
                    Tolak
                </button>
                <button
                    class="btn-modern btn-success"
                    @click="
                        showConfirmYesModal(
                            'Terima',
                            selectedItem.No_Transaksi,
                            selectedItem.Requester_Kode_Karyawan,
                            selectedItem.Approver_Kode_Karyawan
                        )
                    "
                    v-if="selectedItem"
                >
                    <i
                        class="bi bi-check fs-4 d-flex justify-content-center align-items-center me-1"
                    ></i>
                    Setuju
                </button>
            </div>
            <div v-else class="modal-footer-actions p-0 pt-3">
                <button
                    class="btn-modern btn-secondary"
                    @click="showDetailModal = false"
                >
                    Batal
                </button>
            </div>
        </template>
    </el-dialog>

    <!-- Picture Modal -->

    <!-- confirm Modal -->
    <el-dialog
        v-model="showConfirmModal"
        title="Konfirmasi Simpan"
        width="400px"
        center
        :lock-scroll="true"
    >
        <div class="delete-modal-content">
            <i :class="iconConfirm"></i>
            <p>
                {{ titleAlert }} <br />
                <strong>{{ confirmIzinId }}</strong
                >?
            </p>
            <p class="text-muted">Aksi ini tidak dapat dibatalkan.</p>
        </div>
        <template #footer>
            <div class="delete-modal-footer">
                <button
                    class="btn-modern btn-secondary"
                    @click="closeConfirmModal"
                >
                    Batal
                </button>
                <button
                    v-if="decisionIzin == 'Terima'"
                    class="btn-modern btn-success"
                    @click="yesModal"
                >
                    <i
                        v-if="loadingSimpan"
                        class="spinner-border spinner-border-sm me-1"
                    ></i>
                    <i
                        v-else
                        class="bi bi-check d-flex justify-content-center align-items-center fs-4 fw-light me-1"
                    ></i>

                    {{ buttonAlertModal }}
                </button>
                <button v-else class="btn-modern btn-danger" @click="yesModal">
                    <i
                        v-if="!loadingSimpan"
                        class="bi bi-trash d-flex justify-content-center align-items-center me-1"
                    ></i>
                    <i v-else class="spinner-border spinner-border-sm me-1"></i>
                    {{ buttonAlertModal }}
                </button>
            </div>
        </template>
    </el-dialog>

    <!-- Action Modal -->
    <div class="action-menu" v-if="showActionPopup" :style="actionMenuPosition">
        <div class="action-menu-item" @click="showDetail(selectedItem)">
            <i class="bi bi-eye"></i> Lihat Detail
        </div>
        <div class="action-menu-item" @click="editPermission(selectedItem)">
            <i class="bi bi-pencil"></i> Edit
        </div>
        <div
            class="action-menu-item text-danger"
            @click="showDeleteConfirm(selectedItem.No_Transaksi)"
        >
            <i class="bi bi-trash"></i> Hapus
        </div>
        <div class="action-menu-divider"></div>
        <div class="action-menu-item" @click="showActionPopup = false">
            <i class="bi bi-x"></i> Tutup
        </div>
    </div>

    <div
        class="action-menu-backdrop"
        v-if="showActionPopup"
        @click="showActionPopup = false"
    ></div>

    <!-- Modal Assign -->
    <div
        class="modal-overlay"
        :class="{ show: showAssignModal }"
        @click="closeAssignModal"
    >
        <div class="modal-container assign-modal" @click.stop>
            <!-- Header with progress indicator -->
            <div class="modal-header">
                <div class="header-title-section">
                    <h3>
                        <i
                            class="bi bi-calendar-plus me-2 d-flex justify-content-center align-items-center"
                        ></i>
                        Pengajuan Izin
                    </h3>
                    <p class="header-subtitle">
                        Ajukan izin pulang cepat, terlambat, atau sakit
                    </p>
                </div>
                <button
                    class="modal-close"
                    @click="closeAssignModal"
                    aria-label="Close"
                >
                    <i
                        class="bi bi-x-lg d-flex justify-content-center align-items-center"
                    ></i>
                </button>
            </div>

            <div class="modal-body">
                <div v-if="assignStep === 1" class="step-content">
                    <div class="form-section">
                        <!-- Jenis Izin -->
                        <h4 class="section-title">
                            <i
                                class="bi bi-calendar-event me-2 d-flex justify-content-center align-items-center"
                            ></i>
                            Pilih Jenis Izin
                        </h4>

                        <div class="form-group">
                            <div class="permission-type-options row g-2">
                                <div class="col-12 col-md-3">
                                    <div
                                        class="permission-type-card"
                                        :class="{
                                            selected:
                                                assignIzinType === 'izinFull',
                                        }"
                                        @click="handleAssignIzin('izinFull')"
                                    >
                                        <div class="permission-type-icon">
                                            <i class="bi bi-playstation"></i>
                                        </div>
                                        <div class="permission-type-content">
                                            <h6>Tidak Masuk</h6>
                                            <p class="text-muted small">
                                                Tidak masuk full hari
                                            </p>
                                        </div>
                                        <div class="permission-type-check">
                                            <i
                                                class="bi bi-check-circle-fill"
                                                v-if="
                                                    assignIzinType ===
                                                    'izinFull'
                                                "
                                            ></i>
                                            <i class="bi bi-circle" v-else></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div
                                        class="permission-type-card"
                                        :class="{
                                            selected:
                                                assignIzinType ===
                                                'pulangCepat',
                                        }"
                                        @click="handleAssignIzin('pulangCepat')"
                                    >
                                        <div class="permission-type-icon">
                                            <i class="bi bi-house-door"></i>
                                        </div>
                                        <div class="permission-type-content">
                                            <h6>Pulang Cepat</h6>
                                            <p class="text-muted small">
                                                Keluar sebelum waktu pulang
                                            </p>
                                        </div>
                                        <div class="permission-type-check">
                                            <i
                                                class="bi bi-check-circle-fill"
                                                v-if="
                                                    assignIzinType ===
                                                    'pulangCepat'
                                                "
                                            ></i>
                                            <i class="bi bi-circle" v-else></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-3">
                                    <div
                                        class="permission-type-card"
                                        :class="{
                                            selected:
                                                assignIzinType === 'terlambat',
                                        }"
                                        @click="handleAssignIzin('terlambat')"
                                    >
                                        <div class="permission-type-icon">
                                            <i class="bi bi-clock"></i>
                                        </div>
                                        <div class="permission-type-content">
                                            <h6>Terlambat</h6>
                                            <p class="text-muted small">
                                                Datang setelah waktu masuk
                                            </p>
                                        </div>
                                        <div class="permission-type-check">
                                            <i
                                                class="bi bi-check-circle-fill"
                                                v-if="
                                                    assignIzinType ===
                                                    'terlambat'
                                                "
                                            ></i>
                                            <i class="bi bi-circle" v-else></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-3">
                                    <div
                                        class="permission-type-card"
                                        :class="{
                                            selected:
                                                assignIzinType === 'sakit',
                                        }"
                                        @click="handleAssignIzin('sakit')"
                                    >
                                        <div class="permission-type-icon">
                                            <i class="bi bi-heart-pulse"></i>
                                        </div>
                                        <div class="permission-type-content">
                                            <h6>Sakit</h6>
                                            <p class="text-muted small">
                                                Tidak masuk karena sakit
                                            </p>
                                        </div>
                                        <div class="permission-type-check">
                                            <i
                                                class="bi bi-check-circle-fill"
                                                v-if="
                                                    assignIzinType === 'sakit'
                                                "
                                            ></i>
                                            <i class="bi bi-circle" v-else></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <h4 class="section-title">
                            <i
                                class="bi bi-calendar-event me-2 d-flex justify-content-center align-items-center"
                            ></i>
                            Pilih Tanggal
                        </h4>

                        <div
                            v-if="
                                assignIzinType == 'izinFull' ||
                                assignIzinType == 'sakit'
                            "
                            @click="openDialog"
                            class="form-group d-flex cursor-pointer justify-content-start align-items-center"
                        >
                            <div
                                class="form-control d-flex align-items-center justify-content-center cursor-pointer"
                            >
                                <div class="me-2">
                                    <i
                                        class="bi bi-calendar-week d-flex justify-content-center align-items-center"
                                    ></i>
                                </div>
                                <div v-if="!AssignDateRange" class="w-100">
                                    Tekan untuk pilih tanggal
                                </div>
                                <div v-else class="w-100">
                                    {{
                                        AssignDateRange[0] == AssignDateRange[1]
                                            ? formatDateString(
                                                  AssignDateRange[0]
                                              )
                                            : formatDateString(
                                                  AssignDateRange[0]
                                              ) +
                                              " s.d " +
                                              formatDateString(
                                                  AssignDateRange[1]
                                              )
                                    }}
                                </div>
                            </div>
                        </div>
                        <div
                            v-else
                            @click="openDialogSingle"
                            class="form-group d-flex cursor-pointer justify-content-start align-items-center"
                        >
                            <div
                                class="form-control d-flex align-items-center justify-content-center cursor-pointer"
                            >
                                <div class="me-2">
                                    <i
                                        class="bi bi-calendar-week d-flex justify-content-center align-items-center"
                                    ></i>
                                </div>
                                <div v-if="!AssignDateRange" class="w-100">
                                    Tekan untuk pilih tanggal
                                </div>
                                <div v-else class="w-100">
                                    {{ formatDateString(AssignDateRange[0]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="assignStep === 2" class="step-content">
                    <div class="form-section">
                        <h4 class="section-title">
                            <i class="bi bi-gear me-2"></i>
                            Detail Izin
                        </h4>

                        <!-- Time Selection (for early leave/late arrival) -->
                        <div
                            class="form-group mb-4"
                            v-if="
                                assignIzinType === 'pulangCepat' ||
                                assignIzinType === 'terlambat'
                            "
                        >
                            <label class="form-label">
                                {{
                                    assignIzinType === "pulangCepat"
                                        ? "Waktu Pulang"
                                        : "Waktu Datang"
                                }}
                            </label>
                            <div class="input-group px-2 px-md-3 mb-3">
                                <el-slider
                                    @change="console.log(AssignTime)"
                                    v-model="AssignTime"
                                    :min="min"
                                    :max="max"
                                    :step="0.5"
                                    :marks="jamOptions"
                                    :disabled="!max"
                                    :format-tooltip="formatSliderTooltip"
                                    show-stops
                                />
                            </div>
                            <small class="text-muted">
                                {{
                                    assignIzinType === "pulangCepat"
                                        ? "Pilih waktu Anda akan pulang"
                                        : "Pilih waktu Anda akan tiba"
                                }}
                            </small>
                        </div>

                        <!-- Description -->
                        <div class="form-group mb-3">
                            <label class="form-label">Alasan/Keterangan</label>
                            <textarea
                                class="form-control"
                                rows="3"
                                v-model="permissionReason"
                                placeholder="Jelaskan alasan permohonan izin Anda"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div v-if="assignStep === 3" class="step-content">
                    <div class="form-section">
                        <h4 class="section-title">
                            <i
                                class="bi bi-paperclip me-2 d-flex justify-content-center align-items-center"
                            ></i>
                            Lampiran
                        </h4>

                        <!-- File Upload for Sick Leave -->
                        <div
                            class="form-group"
                            v-if="assignIzinType === 'sakit'"
                        >
                            <label class="form-label"
                                >Surat Keterangan Dokter (Wajib)</label
                            >
                            <div class="file-upload-wrapper">
                                <input
                                    type="file"
                                    class="form-control"
                                    ref="doctorLetterInput"
                                    @change="handleFileUpload"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    style="display: none"
                                />
                                <div
                                    v-if="!AssignFile"
                                    class="file-upload-area"
                                    @click="$refs.doctorLetterInput.click()"
                                >
                                    <label
                                        class="file-upload-label d-flex align-items-center justify-content-center"
                                    >
                                        <i
                                            class="bi bi-upload me-2 d-flex align-items-center justify-content-center"
                                        ></i>
                                        <span>Unggah File</span>
                                    </label>
                                </div>
                                <div v-else class="file-display-area">
                                    <span class="file-name">{{
                                        AssignFile.name
                                    }}</span>
                                    <button
                                        @click="removeFile"
                                        class="btn-remove-file"
                                    >
                                        <i class="bi bi-x-circle-fill"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted"
                                >Format: PDF, JPG, PNG (maks. 5MB)</small
                            >
                        </div>
                        <div class="form-group" v-else>
                            <label class="form-label"
                                >Foto Atau File Tambahan (Optional)</label
                            >
                            <div class="file-upload-wrapper">
                                <input
                                    type="file"
                                    class="form-control"
                                    ref="doctorLetterInput"
                                    @change="handleFileUpload"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    style="display: none"
                                />
                                <div
                                    v-if="!AssignFile"
                                    class="file-upload-area"
                                    @click="$refs.doctorLetterInput.click()"
                                >
                                    <label
                                        class="file-upload-label d-flex align-items-center justify-content-center"
                                    >
                                        <i
                                            class="bi bi-upload me-2 d-flex align-items-center justify-content-center"
                                        ></i>
                                        <span>Unggah File</span>
                                    </label>
                                </div>
                                <div v-else class="file-display-area">
                                    <span class="file-name">{{
                                        AssignFile.name
                                    }}</span>
                                    <button
                                        @click="removeFile"
                                        class="btn-remove-file"
                                    >
                                        <i class="bi bi-x-circle-fill"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted"
                                >Format: PDF, JPG, PNG (maks. 5MB)</small
                            >
                        </div>
                        <!-- <div v-else class="text-muted text-center py-4">
                            Tidak ada lampiran yang diperlukan untuk jenis izin
                            ini.
                        </div> -->
                    </div>
                </div>
                <div v-if="assignStep === 4" class="step-content">
                    <div class="form-section">
                        <h4 class="section-title">
                            <i
                                class="bi bi-gear me-2 d-flex justify-content-center align-items-center"
                            ></i>
                            Ringkasan
                        </h4>

                        <div class="form-group AssignRingkasan p-3">
                            <div
                                style="border-bottom: 1px solid #e2e8f0"
                                class="d-flex justify-content-between pb-2 align-items-center mb-4"
                            >
                                <span class="d-flex align-items-center fs-6">
                                    <i
                                        class="bi bi-code-square me-2 fw-bold d-flex justify-content-center align-items-center"
                                    ></i>
                                    <span class="m-0 fw-medium">
                                        Jenis Izin</span
                                    >
                                </span>
                                <div style="color: #6366f1" class="fw-bold">
                                    {{ parseIzin(assignIzinType) }}
                                </div>
                            </div>
                            <div
                                style="border-bottom: 1px solid #e2e8f0"
                                class="d-flex justify-content-between pb-2 align-items-center mb-4"
                            >
                                <span class="d-flex align-items-center fs-6">
                                    <i
                                        class="bi bi-calendar me-2 fw-bold d-flex justify-content-center align-items-center"
                                    ></i>
                                    <span class="m-0 fw-medium"> Tanggal</span>
                                </span>
                                <div
                                    style="color: #6366f1"
                                    class="fw-bold"
                                ></div>
                            </div>
                            <div
                                style="border-bottom: 1px solid #e2e8f0"
                                class="d-flex justify-content-between pb-2 align-items-center mb-4"
                            >
                                <span class="d-flex align-items-center fs-6">
                                    <i
                                        class="bi bi-blockquote-right me-2 fw-bold d-flex justify-content-center align-items-center"
                                    ></i>
                                    <span class="m-0 fw-medium"> Alasan</span>
                                </span>
                                <div style="color: #6366f1" class="fw-bold">
                                    {{ permissionReason }}
                                </div>
                            </div>
                            <div
                                style="border-bottom: 1px solid #e2e8f0"
                                class="d-flex justify-content-between pb-2 align-items-center mb-4"
                            >
                                <span class="d-flex align-items-center fs-6">
                                    <i
                                        class="bi bi-clock me-2 fw-bold d-flex justify-content-center align-items-center"
                                    ></i>
                                    <span class="m-0 fw-medium">Waktu</span>
                                </span>
                                <div style="color: #6366f1" class="fw-bold">
                                    {{
                                        assignIzinType == "izinFull" ||
                                        assignIzinType == "sakit"
                                            ? "Tidak ada"
                                            : formatTimeForSubmission(
                                                  AssignTime
                                              )
                                                  .split(":")
                                                  .slice(0, 2)
                                                  .join(":")
                                    }}
                                </div>
                            </div>
                            <div
                                class="d-flex justify-content-between pb-2 align-items-center mb-1"
                            >
                                <span class="d-flex align-items-center fs-6">
                                    <i
                                        class="bi bi-textarea me-2 fw-bold d-flex justify-content-center align-items-center"
                                    ></i>
                                    <span class="m-0 fw-medium">Lampiran</span>
                                </span>
                                <div style="color: #6366f1" class="fw-bold">
                                    {{
                                        AssignFile
                                            ? AssignFile.name
                                            : "Tidak ada"
                                    }}
                                </div>
                            </div>
                            <!-- <small class="text-muted"
                                >Format: PDF, JPG, PNG (maks. 5MB)</small
                            > -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="footer-actions">
                    <button
                        v-if="assignStep > 1"
                        class="btn-modern btn-secondary"
                        @click="prevAssignStep"
                    >
                        <i
                            class="bi bi-arrow-left me-2 d-flex justify-content-center align-items-center"
                        ></i>
                        Kembali
                    </button>

                    <button
                        class="btn-modern btn-secondary"
                        @click="closeAssignModal"
                    >
                        Batal
                    </button>

                    <button
                        v-if="assignStep < 4"
                        class="btn-modern btn-primary"
                        @click="nextAssignStep"
                        :disabled="!canProceedToNextStep"
                    >
                        Lanjut
                        <i
                            class="bi bi-arrow-right ms-2 d-flex justify-content-center align-items-center"
                        ></i>
                    </button>

                    <button
                        v-if="assignStep === 4"
                        class="btn-modern btn-success"
                        @click="submitData"
                    >
                        <i
                            class="bi bi-check-lg me-2 d-flex justify-content-center align-items-center"
                        ></i>
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Single -->
    <el-dialog
        v-model="dialogVisible"
        title="Pilih Tanggal Mulai dan Selesai"
        width="480px"
        align-center
        :lock-scroll="true"
    >
        <el-calendar v-model="calendarDate" :disabled-date="disablePastDates">
            <template #date-cell="{ data }">
                <div
                    class="cell-date-wrapper"
                    :class="getCellClass(data)"
                    @click="handleDateClick(data)"
                >
                    <div
                        class="date-cell"
                        :class="{
                            'is-disabled-custom': disablePastDates(data.date),
                        }"
                    >
                        {{ data.day.split("-")[2] }}
                    </div>
                </div>
            </template>
        </el-calendar>

        <template #footer>
            <div class="dialog-footer gap-3 d-flex justify-content-end">
                <el-button
                    @click="handleCancel"
                    class="btn-secondary p-1 rounded-3"
                    >Batal</el-button
                >
                <el-button
                    class="shiningEffect btn-success py-0 rounded-3 px-1 py-1"
                    :disabled="!tempEndDate"
                    @click="confirmDate('range')"
                >
                    Pilih Tanggal
                </el-button>
            </div>
        </template>
    </el-dialog>

    <!-- Calendar Range -->
    <el-dialog
        v-model="dialogVisibleSingle"
        title="Pilih Tanggal"
        width="480px"
        align-center
        :lock-scroll="true"
    >
        <el-calendar v-model="calendarDate" :disabled-date="disablePastDates">
            <template #date-cell="{ data }">
                <div
                    class="cell-date-wrapper"
                    :class="getCellClass(data)"
                    @click="handleDateClickSingle(data)"
                >
                    <div
                        class="date-cell"
                        :class="{
                            'is-disabled-custom': disablePastDates(data.date),
                        }"
                    >
                        {{ data.day.split("-")[2] }}
                    </div>
                </div>
            </template>
        </el-calendar>

        <template #footer>
            <div class="dialog-footer gap-3 d-flex justify-content-end">
                <el-button
                    @click="handleCancel"
                    class="btn-secondary p-1 rounded-3"
                    >Batal</el-button
                >
                <el-button
                    class="shiningEffect btn-success py-0 rounded-3 px-1 py-1"
                    :disabled="!tempEndDate"
                    @click="confirmDate('single')"
                >
                    Pilih Tanggal
                </el-button>
            </div>
        </template>
    </el-dialog>

    <!-- Delete Modal -->
    <el-dialog
        v-model="showDeleteModal"
        title="Konfirmasi Hapus"
        width="400px"
        center
        :lock-scroll="true"
    >
        <div class="delete-modal-content">
            <i class="bi bi-exclamation-triangle warning-icon"></i>
            <p>
                Anda yakin ingin Mensetujui pengajuan izin
                <strong>{{ deleteItemId }}</strong
                >?
            </p>
            <p class="text-muted">Aksi ini tidak dapat dibatalkan.</p>
        </div>
        <template #footer>
            <div class="delete-modal-footer">
                <button
                    class="btn-modern btn-secondary"
                    @click="showDeleteModal = false"
                >
                    Batal
                </button>
                <button class="btn-modern btn-danger" @click="confirmDelete">
                    <i
                        class="bi bi-trash d-flex justify-content-center align-items-center"
                    ></i>
                    Simpan
                </button>
            </div>
        </template>
    </el-dialog>
</template>

<script scope>
import axios from "axios";
import {
    ElDialog,
    ElCalendar,
    ElMessage,
    ElSteps,
    ElTag,
    ElStep,
    ElTooltip,
    ElNotification,
    ElSlider,
} from "element-plus";
import "element-plus/dist/index.css";
import { capitalize } from "vue";
// import DotLottieVue from 'dotlottie-vue'; // Uncomment if you are using dotlottie-vue

export default {
    components: {
        ElDialog,
        ElCalendar,
        ElMessage,
        ElSteps,
        ElStep,
        ElTooltip,
        ElTag,
        ElNotification,
        ElSlider,
        // DotLottieVue, // Uncomment if you are using dotlottie-vue
    },
    props: {
        dataShift: Array,
        namaKaryawan: String,
    },
    data() {
        return {
            filterDropdown: "",

            dialogFilter: false,
            selectedDateFilter: null,
            tempStartDateFilter: null,

            loadingSimpan: false,
            titleAlert: "",
            confirmIzinId: null,
            decisionIzin: null,
            requesterConfirm: null,
            approverConfirm: null,
            buttonConfirmModal: null,
            iconConfirm: "",
            showConfirmModal: false,
            yesModal: null,

            showPictureModal: false,
            showDetailModal: false,
            selectedItem: null,
            showDeleteModal: false,
            deleteItemId: null,
            showActionPopup: false,
            actionMenuPosition: { top: "0", left: "0" },
            showSearchSuggestions: false,
            searchSuggestions: [
                "Izin Sakit",
                "Izin Terlambat",
                "Pulang Cepat",
                "Izin Pribadi",
            ],
            viewMode: "table",
            assignStep: 1,
            loading: true,
            searchQuery: "",
            currentPage: 1,
            itemsPerPage: 8,
            currentDate: new Date(),
            showAssignModal: false,
            dayNight: "Siang",
            permissionRecords: [],

            min: null,
            max: null,
            jamOptions: [],

            assignIzinType: null,
            AssignDateRange: null,
            AssignTime: null,
            permissionReason: "",
            AssignFile: null,

            dialogVisible: false,
            dialogVisibleSingle: false,
            tempStartDate: null,
            tempEndDate: null,

            DataIzin: null,

            loadingAttachment: false,
            min: null,
            max: null,
            timeMarks: {
                8: "08",
                9: "09",
                10: "10",
                11: "11",
                12: "12",
                13: "13",
                14: "14",
                15: "15",
                16: "16",
            },
            windowWidth: window.innerWidth, // For responsive design checks
            approvalData: ["M.REZA: Y, RIDHO RAHMAT: T, BUDI SANTOSO: N"],
        };
    },
    computed: {
        parsedData() {
            const allApprovals = this.approvalData.join(", ").split(", ");
            return allApprovals.map((item) => {
                const [name, status] = item.split(": ");
                return { name: name.trim(), status: status.trim() };
            });
        },
        tooltipContent() {
            const totalApprovals = this.parsedData.length;
            const approvedCount = this.parsedData.filter(
                (item) => item.status === "Y" || item.status === "T"
            ).length;
            const rejectedCount = this.parsedData.filter(
                (item) => item.status === "T"
            ).length;
            const progressCount = this.parsedData.filter(
                (item) => item.status === "N"
            ).length;
            const approvalRate = Math.round(
                (approvedCount / totalApprovals) * 100
            );

            // Find active step
            const firstRejected = this.parsedData.findIndex(
                (item) => item.status === "T"
            );
            const firstPending = this.parsedData.findIndex(
                (item) => item.status === "N"
            );
            const activeIndex =
                firstRejected >= 0
                    ? firstRejected
                    : firstPending >= 0
                    ? firstPending
                    : -1;

            return `
    <div class="approval-tooltip">
        <div class="tooltip-header">
            <span class="header-title">Progress Persetujuan</span>
            <span class="approval-rate">
                <span class="rate-value">${approvalRate}%</span>
                <span class="rate-label">Complete</span>
            </span>
        </div>

        <div class="approval-steps">
            ${this.parsedData
                .map((item, index) => {
                    const isActive = index === activeIndex;
                    const isApproved = item.status === "Y";
                    const isRejected = item.status === "T";
                    const isPending = item.status === "N";

                    return `
                    <div class="approval-step ${isActive ? "active-step" : ""}">
                        <div class="step-icon ${
                            isApproved
                                ? "approved"
                                : isRejected
                                ? "rejected"
                                : isPending
                                ? "pending"
                                : "waiting"
                        }">
                            ${
                                isApproved
                                    ? "✓"
                                    : isRejected
                                    ? "✕"
                                    : isPending
                                    ? "⏳"
                                    : "?"
                            }
                        </div>
                        <div class="step-details">
                            <div class="step-name">${item.name}</div>
                            <div class="step-status ${
                                isApproved
                                    ? "approved"
                                    : isRejected
                                    ? "rejected"
                                    : isPending
                                    ? "pending"
                                    : "waiting"
                            }">
                                ${
                                    isApproved
                                        ? "Disetujui"
                                        : isRejected
                                        ? "Ditolak"
                                        : isPending
                                        ? "Dalam Proses"
                                        : "Menunggu"
                                }
                            </div>
                        </div>
                        ${isActive ? '<div class="step-indicator"></div>' : ""}
                    </div>
                `;
                })
                .join("")}
        </div>

        <div class="approval-summary">
            <div class="summary-item approved">
                <div class="summary-count">${approvedCount}</div>
                <div class="summary-label">Disetujui</div>
            </div>
            <div class="summary-item pending">
                <div class="summary-count">${progressCount}</div>
                <div class="summary-label">Proses</div>
            </div>
            <div class="summary-item rejected">
                <div class="summary-count">${rejectedCount}</div>
                <div class="summary-label">Ditolak</div>
            </div>
        </div>
    </div>

    <style>
        .approval-tooltip {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 320px;
            padding: 0;
            border-radius: 8px;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
        }

        .tooltip-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 8px 8px 0 0;
        }

        .header-title {
            font-weight: 600;
            font-size: 14px;
            color: #1e293b;
        }

        .approval-rate {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .rate-value {
            font-weight: 700;
            font-size: 16px;
            color: #3b82f6;
        }

        .rate-label {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        .approval-steps {
            padding: 12px 16px;
            max-height: 220px;
            overflow-y: auto;
        }

        .approval-step {
            display: flex;
            align-items: center;
            padding: 10px 8px;
            margin-bottom: 6px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .active-step {
            background: #f1f5f9;
            box-shadow: 0 0 0 1px #e2e8f0;
        }

        .step-icon {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 12px;
            font-weight: 600;
            flex-shrink: 0;
        }

        .approved {
            background: linear-gradient(135deg, #34d399 0%, #059669ce 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(167, 139, 250, 0.3);
        }

        .rejected {
            background: linear-gradient(135deg, #f472b5 0%, #ec489ad3 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(244, 114, 182, 0.3);
        }

        .pending {
            background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
            color: white;
            box-shadow: 0 2px 4px rgba(129, 140, 248, 0.3);
        }

        .waiting {
            background: linear-gradient(135deg, #818cf8 0%, #6365f1ce 100%);
            color: #64748b;
        }

        .step-details {
            flex: 1;
            min-width: 0;
        }

        .step-name {
            font-weight: 500;
            font-size: 13px;
            color: #1e293b;
            margin-bottom: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .step-status {
            font-size: 11px;
            font-weight: 500;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .step-status.approved {
            color: white;
             background: linear-gradient(135deg, #6ee7b7 0%, #10b981ce 100%);
        }

        .step-status.rejected {
            color: white;
                       background: linear-gradient(135deg, #f472b5 0%, #ec489ad3 100%);

        }

        .step-status.pending {
            color: #3730a3;
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        }

        .step-status.waiting {
            color: #475569;
             background: linear-gradient(135deg, #818cf8 0%, #6365f1ce 100%);
        }

        .step-indicator {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #3b82f6;
            margin-left: 8px;
            animation: pulse 1.5s infinite;
            flex-shrink: 0;
        }

        .approval-summary {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            padding: 12px 16px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 0 0 8px 8px;
        }

        .summary-item {
            text-align: center;
            padding: 8px;
            border-radius: 6px;
            background: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .summary-count {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .summary-item.approved .summary-count {
            color: #8b5cf6;
        }

        .summary-item.pending .summary-count {
            color: #6366f1;
        }

        .summary-item.rejected .summary-count {
            color: #ec4899;
        }

        .summary-label {
            font-size: 11px;
            font-weight: 500;
            color: #64748b;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.1); }
        }
            </style>
        `;
        },
        filteredSuggestions() {
            if (!this.searchQuery) {
                return this.searchSuggestions;
            }
            return this.searchSuggestions.filter((suggestion) =>
                suggestion
                    .toLowerCase()
                    .includes(this.searchQuery.toLowerCase())
            );
        },
        filteredPermissions() {
            let filtered = this.permissionRecords.map((emp) => ({ ...emp }));
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(
                    (item) =>
                        item.No_Transaksi.toLowerCase().includes(query) ||
                        this.parseIzin(item.Tipe_Izin)
                            .toLowerCase()
                            .includes(query) ||
                        item.Alasan.toLowerCase().includes(query) ||
                        this.maknaStatus(item.Status)
                            .toLowerCase()
                            .includes(query)
                );
            }
            if (this.filterDropdown) {
                const query = this.filterDropdown.toLowerCase();
                filtered = filtered.filter(
                    (item) =>
                        item.No_Transaksi.toLowerCase().includes(query) ||
                        this.parseIzin(item.Tipe_Izin)
                            .toLowerCase()
                            .includes(query) ||
                        item.Alasan.toLowerCase().includes(query) ||
                        this.maknaStatus(item.Status)
                            .toLowerCase()
                            .includes(query)
                );
            }

            if (this.selectedDateFilter) {
                filtered = filtered.filter((item) => {
                    // Convert all dates to Date objects for reliable comparison
                    const selectedDate = new Date(this.selectedDateFilter);
                    const startDate = new Date(item.Tanggal_Mulai);
                    const endDate = new Date(item.Tanggal_Selesai);

                    // Check if selectedDate is greater than or equal to Tanggal_Mulai
                    // AND less than or equal to Tanggal_Selesai
                    return (
                        (selectedDate >= startDate &&
                            selectedDate <= endDate) ||
                        null
                    );
                });
            }

            return filtered;
        },
        totalPages() {
            return Math.ceil(
                this.filteredPermissions.length / this.itemsPerPage
            );
        },
        paginatedPermissions() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredPermissions.slice(start, end);
        },
        visiblePages() {
            const maxVisible = 5;
            const half = Math.floor(maxVisible / 2);
            let start = Math.max(1, this.currentPage - half);
            let end = Math.min(this.totalPages, start + maxVisible - 1);

            if (end - start + 1 < maxVisible) {
                start = Math.max(1, end - maxVisible + 1);
            }

            return Array.from({ length: end - start + 1 }, (_, i) => start + i);
        },

        assignSelectedShift() {
            return this.assignIzinType && this.AssignDateRange;
        },
    },
    methods: {
        capitalize(value) {
            if (!value) return "";
            const words = value.split(" ");
            const capitalizedWords = words.map((word) => {
                if (!word) return "";
                return (
                    word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
                );
            });
            return capitalizedWords.join(" ");
        },
        pisahTanggal(tanggalIzin) {
            if (!tanggalIzin) return null;

            // 1. Handle date with time (18 July 2025 18:30)
            const dateWithTimeMatch = tanggalIzin.match(
                /^(\d{1,2}) ([A-Za-z]+) (\d{4}) (\d{1,2}):(\d{2})$/
            );
            if (dateWithTimeMatch) {
                const [, day, month, year, hours, minutes] = dateWithTimeMatch;
                const dateStr = `${month} ${day}, ${year} ${hours}:${minutes}`;
                const date = new Date(dateStr);

                if (!isNaN(date.getTime())) {
                    return {
                        startDate: new Date(date),
                        endDate: new Date(date),
                    };
                }
            }

            // 2. Handle single date without time (18 July 2025)
            const singleDateMatch = tanggalIzin.match(
                /^(\d{1,2}) ([A-Za-z]+) (\d{4})$/
            );
            if (singleDateMatch) {
                const [, day, month, year] = singleDateMatch;
                const dateStr = `${month} ${day}, ${year}`;
                const date = new Date(dateStr);

                if (!isNaN(date.getTime())) {
                    return {
                        startDate: new Date(date.setHours(0, 0, 0, 0)),
                        endDate: new Date(date.setHours(23, 59, 59, 999)),
                    };
                }
            }

            // 3. Handle date range (16-24 July 2025)
            const rangeMatch = tanggalIzin.match(
                /^(\d{1,2})-(\d{1,2}) ([A-Za-z]+) (\d{4})$/
            );
            if (rangeMatch) {
                const [, startDay, endDay, month, year] = rangeMatch;
                const startStr = `${month} ${startDay}, ${year}`;
                const endStr = `${month} ${endDay}, ${year}`;

                const startDate = new Date(startStr);
                const endDate = new Date(endStr);

                if (!isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                    startDate.setHours(0, 0, 0, 0);
                    endDate.setHours(23, 59, 59, 999);
                    return { startDate, endDate };
                }
            }

            console.error("Unrecognized date format:", tanggalIzin);
            return null;
        },
        parseStatus(status, app) {
            let nama = app.split(" ").slice(0, 1).join(" ");
            if (status == "Ditolak") {
                return `Ditolak oleh ${nama}`;
            } else if (status == "Selesai") {
                return "Disetujui";
            } else {
                return `Diproses oleh ${nama}`;
            }
        },
        getStatusTagType(status, approver) {
            if (status === null) return "warning";
            if (status === "Y") return "success";
            if (status === "T") return "danger";
            return "info";
        },

        getActiveStep(statusList) {
            const parsed = this.parsedDataArray(statusList);
            const lastApprovedIndex = parsed.findLastIndex(
                (item) => item.status === "Y" || item.status === "T"
            );
            return lastApprovedIndex + 1;
        },

        getStepStatus(status) {
            if (status === "Y") return "success";
            if (status === "T") return "error";
            return "wait";
        },

        getStepIcon(status) {
            if (status === "Y") return "el-icon-check";
            if (status === "T") return "el-icon-close";
            return "el-icon-more";
        },
        parsedDataArray(data) {
            const allApprovals = data.split(", ");
            return allApprovals.map((item) => {
                const [name, status] = item.split(": ");
                return {
                    name: name.split(" ").slice(0, 1).join(" ").trim(),
                    status: status.trim(),
                };
            });
        },
        async viewAttachment(attachmentPath) {
            if (!attachmentPath) {
                ElMessage({
                    message: "Tidak ada lampiran untuk ditampilkan.",
                    type: "warning",
                });
                return;
            }

            this.loadingAttachment = true;

            try {
                const response = await axios.post(
                    "/izin/getAttachmentUrl",
                    {
                        path: attachmentPath,
                    },
                    {
                        responseType: "blob", // Penting untuk file download
                    }
                );

                if (response.status === 200) {
                    // Buat blob URL untuk download
                    const blob = new Blob([response.data]);
                    const url = window.URL.createObjectURL(blob);

                    // Extract filename dari response headers atau path
                    const fileName = attachmentPath.substring(
                        attachmentPath.lastIndexOf("/") + 1
                    );

                    // Trigger download
                    const downloadLink = document.createElement("a");
                    downloadLink.href = url;
                    downloadLink.download = fileName;
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);

                    // Cleanup
                    window.URL.revokeObjectURL(url);

                    ElMessage({
                        message: "File berhasil didownload.",
                        type: "success",
                    });
                }
            } catch (error) {
                console.error("Error downloading file:", error);
                ElMessage({
                    message: "Terjadi kesalahan saat mendownload file.",
                    type: "error",
                });
            } finally {
                this.loadingAttachment = false;
            }
        },
        maknaStatus(status) {
            if (status === "Y") {
                return "Disetujui";
            } else if (status === null || status === undefined) {
                return "Menunggu Persetujuan";
            } else if (status === "T") {
                return "Ditolak";
            }
            return "";
        },
        showConfirmYesModal(decision, No_Transaksi, requester, approver) {
            this.titleAlert = "Anda yakin ingin menerima pengajuan izin";
            this.confirmIzinId = No_Transaksi;
            this.decisionIzin = decision;
            this.requesterConfirm = requester;
            this.approverConfirm = approver;
            this.buttonAlertModal = "Setuju";
            this.iconConfirm = "bi bi-check-circle success-icon";
            this.yesModal = () => this.confirmIzin();
            this.showConfirmModal = true;
        },
        showConfirmNoModal(decision, No_Transaksi, requester, approver) {
            this.titleAlert = "Anda yakin ingin Menolak pengajuan izin";
            this.confirmIzinId = No_Transaksi;
            this.decisionIzin = decision;
            this.requesterConfirm = requester;
            this.approverConfirm = approver;
            this.buttonAlertModal = "Tolak";
            this.iconConfirm = "bi bi-exclamation-triangle warning-icon";
            this.yesModal = () => this.confirmIzin();
            this.showConfirmModal = true;
        },
        closeConfirmModal() {
            this.showConfirmModal = false;
            this.titleAlert = "";
            this.confirmIzinId = null;
            this.requesterConfirm = null;
            this.approverConfirm = null;
            this.buttonAlertModal = null;
            this.iconConfirmModal = "";
            this.decisionIzin = null;
            this.yesModal = null;
        },
        async confirmIzin() {
            try {
                this.loadingSimpan = true;
                const formData = new FormData();
                formData.append("No_Transaksi", this.confirmIzinId);
                formData.append("tipeConfirm", this.decisionIzin);
                formData.append("requester", this.requesterConfirm);
                formData.append("approver", this.approverConfirm);

                const response = await axios.post(
                    "/izin/confirmIzin",
                    formData
                );

                if (response.status === 200) {
                    ElNotification({
                        title: "Success",
                        message:
                            response.data.message ||
                            "Konfirmasi izin berhasil!",
                        type: "success",
                    });
                    this.getData();
                } else {
                    ElMessage({
                        message:
                            response.data.message ||
                            "Terjadi kesalahan saat konfirmasi izin.",
                        type: "warning",
                    });
                }
            } catch (error) {
                console.error("Error in ConfirmIzin:", error);

                let errorMessage =
                    "Terjadi kesalahan saat menyimpan, silakan coba lagi.";
                if (
                    error.response &&
                    error.response.data &&
                    error.response.data.message
                ) {
                    errorMessage = error.response.data.message;
                }

                ElMessage({
                    message: errorMessage,
                    type: "error",
                    customStyle: {
                        "z-index": "1050",
                    },
                });
            } finally {
                this.showDetailModal = false;
                this.showConfirmModal = false;
                this.loadingSimpan = false;
            }
        },
        konfirmApprov() {
            // console.log(object);
        },
        parseIzin(tipe) {
            if (tipe == "izinFull") {
                return "Izin Pribadi";
            } else if (tipe == "sakit") {
                return "Izin Sakit";
            } else if (tipe == "pulangCepat") {
                return "Izin Pulang Cepat";
            } else if (tipe == "terlambat") {
                return "Izin Terlambat";
            } else if (tipe == "cuti") {
                return "Cuti";
            } else if (tipe == "cutiHutang") {
                return "Cuti";
            }
        },
        async getJamKerja() {
            try {
                const tanggal = this.AssignDateRange;
                const response = await axios.get("/izin/getKerja", {
                    params: tanggal,
                });
                if (response.status == 200) {
                    this.min = response.data.data.min;
                    this.max = response.data.data.max;
                    this.AssignTime = response.data.data.min;
                    this.JamOptions = response.data.data.JamOptions;
                }
            } catch (error) {
                console.log(error);
            }
        },

        log() {
            // console.log(this.yesModal);
        },
        formatTimeForSubmission(sliderValue) {
            const numValue = parseFloat(sliderValue);

            if (isNaN(numValue) || !isFinite(numValue)) {
                return "00:00:00";
            }

            const hours = Math.floor(numValue);
            const minutes = (numValue - hours) * 60;

            const formattedHours = String(
                Math.max(0, Math.min(23, hours))
            ).padStart(2, "0");
            const formattedMinutes = String(
                Math.max(0, Math.min(59, minutes))
            ).padStart(2, "0");

            return `${formattedHours}:${formattedMinutes}:00`;
        },

        // async submitData() {
        //     // console.log(this.AssignDateRange);
        //     const formData = new FormData();
        //     formData.append("jenisIzin", this.assignIzinType);
        //     if (this.AssignDateRange && Array.isArray(this.AssignDateRange)) {
        //         this.AssignDateRange.forEach((date) => {
        //             formData.append("tanggal[]", date); // Ini yang benar!
        //         });
        //     } else {
        //         console.warn(
        //             "AssignDateRange is not a valid array or is empty."
        //         );
        //     }
        //     const formattedTime = this.formatTimeForSubmission(this.AssignTime);
        //     formData.append("waktu", formattedTime);
        //     formData.append("Alasan", this.permissionReason);
        //     if (this.AssignFile instanceof File) {
        //         formData.append("file", this.AssignFile);
        //         console.log("Appending 'file' to FormData as a File object.");
        //     } else {
        //         console.log(
        //             "this.AssignFile is NOT a File object. NOT appending 'file' to FormData."
        //         );
        //     }

        //     try {
        //         const response = await axios.post("/izin/submit", formData, {
        //             headers: { "Content-Type": "multipart/form-data" },
        //         });
        //         if (response.status == 200) {
        //             console.log(response.data.data);
        //         }
        //     } catch (error) {
        //         console.log(error);
        //         if (error.response.status == 422) {
        //             ElMessage({
        //                 message: error.response.data.errors,
        //                 type: "warning",
        //                 customStyle: {
        //                     "z-index": "1050",
        //                 },
        //             });
        //         }
        //     }
        // },
        async getData() {
            try {
                const response = await axios.get("/izin/getDataIzinDH");

                if (response.status == 200) {
                    this.permissionRecords = response.data.data;
                    // console.log(this.permissionRecords);
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loading = false;
            }
        },
        canProceedToNextStep() {
            if (this.assignStep === 1) {
                return (
                    this.assignIzinType !== null &&
                    this.AssignDateRange !== null &&
                    this.AssignDateRange.length > 0
                );
            }
            if (this.assignStep === 2) {
                if (
                    this.assignIzinType === "pulangCepat" ||
                    this.assignIzinType === "terlambat"
                ) {
                    return (
                        this.AssignTime !== null && this.permissionReason !== ""
                    );
                }

                return this.permissionReason !== "";
            }
            if (this.assignStep === 3) {
                if (this.assignIzinType === "sakit") {
                    return this.AssignFile !== null;
                }
                return true;
            }
            return true;
        },
        formatSliderTooltip(value) {
            const hours = Math.floor(value);
            const minutes = (value % 1) * 60;
            const formattedHours = String(hours).padStart(2, "0");
            const formattedMinutes = String(minutes).padStart(2, "0");
            return `${formattedHours}:${formattedMinutes}`;
        },
        formatDateToDMY(datetimeString) {
            if (!datetimeString) {
                return ""; // Return empty string if input is empty or null
            }

            const date = new Date(datetimeString);

            if (isNaN(date.getTime())) {
                console.warn("Invalid date string provided:", datetimeString);
                return "";
            }

            const day = String(date.getDate()).padStart(2, "0");
            const year = date.getFullYear();

            const monthNames = [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "Mei",
                "Jun",
                "Jul",
                "Agu",
                "Sep",
                "Okt",
                "Nov",
                "Des",
            ];
            const month = monthNames[date.getMonth()];

            return `${day} ${month} ${year}`;
        },
        formatDateToString(date) {
            if (!date) return "";
            const year = date.getFullYear();
            const month = (date.getMonth() + 1).toString().padStart(2, "0");
            const day = date.getDate().toString().padStart(2, "0");
            return `${year}-${month}-${day}`;
        },
        formatFullDate(dateString) {
            const [year, month, day] = dateString.split("-").map(Number);
            const date = new Date(Date.UTC(year, month - 1, day));
            const options = {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
                timeZone: "UTC",
            };
            return date.toLocaleDateString("id-ID", options);
        },
        formatDateString(dateString) {
            if (!dateString) return "";
            try {
                const date = new Date(dateString);
                const options = {
                    day: "numeric",
                    month: "long",
                    year: "numeric",
                };
                return new Intl.DateTimeFormat("id-ID", options).format(date);
            } catch (error) {
                console.error("Error formatting date:", error);
                return dateString; // Fallback
            }
        },
        disablePastDates(date) {
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Normalize today to the start of the day

            const dateToCheck = new Date(date);
            dateToCheck.setHours(0, 0, 0, 0); // Normalize the date to check to the start of the day

            return dateToCheck.getTime() < today.getTime();
        },
        openDialog() {
            if (this.AssignDateRange && this.AssignDateRange.length === 2) {
                const [startStr, endStr] = this.AssignDateRange;
                this.tempStartDate = new Date(startStr + "T00:00:00");
                this.tempEndDate = new Date(endStr + "T00:00:00");
                this.calendarDate = new Date(startStr + "T00:00:00");
            } else {
                this.tempStartDate = null;
                this.tempEndDate = null;
                this.calendarDate = new Date();
            }
            this.dialogVisible = true;
        },
        openDialogSingle() {
            if (this.AssignDateRange && this.AssignDateRange.length === 2) {
                const [startStr] = this.AssignDateRange;
                this.tempStartDate = new Date(startStr + "T00:00:00");
                this.tempEndDate = new Date(startStr + "T00:00:00");
                this.calendarDate = new Date(startStr + "T00:00:00");
            } else {
                this.tempStartDate = null;
                this.tempEndDate = null;
                this.calendarDate = new Date();
            }
            this.dialogVisibleSingle = true;
        },
        handleAssignIzin(type) {
            this.AssignDateRange = null;
            this.assignIzinType = type;
            this.tempStartDate = null;
            this.tempEndDate = null;
            this.AssignFile = null;
        },
        handleCancel() {
            this.dialogVisible = false;
            this.dialogVisibleSingle = false;
            this.tempStartDate = null;
            this.tempEndDate = null;
        },
        handleDateClick(data) {
            const clickedDate = data.date;
            if (this.disablePastDates(clickedDate)) {
                ElMessage({
                    message: "Tanggal ini tidak bisa dipilih.",
                    type: "warning",
                });
                return;
            }

            if (this.tempStartDate && this.tempEndDate) {
                this.tempStartDate = clickedDate;
                this.tempEndDate = null;
            } else if (!this.tempStartDate) {
                this.tempStartDate = clickedDate;
            } else {
                if (clickedDate.getTime() < this.tempStartDate.getTime()) {
                    this.tempEndDate = this.tempStartDate;
                    this.tempStartDate = clickedDate;
                } else {
                    this.tempEndDate = clickedDate;
                }
            }
        },
        handleDateClickSingle(data) {
            const clickedDate = data.date;
            if (this.disablePastDates(clickedDate)) {
                ElMessage({
                    message: "Tanggal ini tidak bisa dipilih.",
                    type: "warning",
                });
                return;
            }
            this.tempStartDate = clickedDate;
            this.tempEndDate = clickedDate;
        },
        getCellClass(data) {
            const classes = {};
            const currentDate = data.date.setHours(0, 0, 0, 0);

            const start = this.tempStartDate
                ? this.tempStartDate.setHours(0, 0, 0, 0)
                : null;
            const end = this.tempEndDate
                ? this.tempEndDate.setHours(0, 0, 0, 0)
                : null;

            if (start && currentDate === start) classes["is-start"] = true;
            if (end && currentDate === end) classes["is-end"] = true;

            if (start && end && currentDate > start && currentDate < end) {
                classes["is-in-range"] = true;
            }
            if (this.disablePastDates(data.date)) {
                classes["is-disabled-custom"] = true;
            }

            return classes;
        },
        confirmDate(jenis) {
            this.loading = true;
            if (jenis == "single") {
                if (!this.tempEndDate) {
                    ElMessage({
                        message: "Silakan pilih tanggal izin.",
                        type: "warning",
                    });
                    this.loading = false;
                    return;
                }

                this.AssignDateRange = [
                    this.formatDateToString(this.tempEndDate),
                    this.formatDateToString(this.tempEndDate),
                ];
            } else {
                if (!this.tempStartDate || !this.tempEndDate) {
                    ElMessage({
                        message: "Silakan pilih tanggal mulai dan selesai.",
                        type: "warning",
                    });
                    this.loading = false;
                    return;
                }
                this.AssignDateRange = [
                    this.formatDateToString(this.tempStartDate),
                    this.formatDateToString(this.tempEndDate),
                ];
            }

            this.dialogVisible = false;
            this.dialogVisibleSingle = false;
            this.loading = false;
            // console.log(this.AssignDateRange);
        },

        nextAssignStep() {
            if (this.canProceedToNextStep()) {
                if (this.assignStep == 1) {
                    if (
                        this.assignIzinType == "pulangCepat" ||
                        this.assignIzinType == "terlambat"
                    ) {
                        this.getJamKerja().then(() => {
                            this.assignStep++;
                        });
                    } else {
                        this.assignStep++;
                    }
                } else {
                    this.assignStep++;
                }
            } else {
                ElMessage({
                    message: "Harap lengkapi pilihan Anda sebelum melanjutkan.",
                    type: "warning",
                });
            }
        },
        prevAssignStep() {
            if (this.assignStep > 1) {
                this.assignStep--;
            }
        },

        // --- File Upload Methods ---
        handleFileUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const allowedTypes = [
                    "application/pdf",
                    "image/jpeg",
                    "image/jpg",
                    "image/png",
                ];
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (!allowedTypes.includes(file.type)) {
                    ElMessage({
                        message:
                            "Format file tidak didukung. Gunakan PDF, JPG, atau PNG.",
                        type: "error",
                    });
                    this.AssignFile = null;
                    event.target.value = "";
                    return;
                }

                if (file.size > maxSize) {
                    ElMessage({
                        message: "Ukuran file terlalu besar. Maksimal 5MB.",
                        type: "error",
                    });
                    this.AssignFile = null;
                    event.target.value = "";
                    return;
                }

                this.AssignFile = file;
            } else {
                this.AssignFile = null;
            }
        },
        removeFile() {
            this.AssignFile = null;
            if (this.$refs.doctorLetterInput) {
                this.$refs.doctorLetterInput.value = "";
            }
        },

        // --- Data and UI State Management Methods ---
        deleteAlertButton(transactionId) {
            // console.log("Delete transaction:", transactionId);
            ElMessage({
                message: `Transaksi ${transactionId} akan dihapus.`,
                type: "info",
            });
            // Implement actual deletion logic here
        },
        storeAlertButton() {
            console.log("Storing alert/permission...");
            // You can access this.AssignFile here if it's needed for submission
            ElNotification({
                title: "Berhasil",
                message: "Pengajuan izin berhasil disimpan!",
                type: "success",
                position: "bottom-right",
            });
            this.closeAssignModal();
            // Implement actual storage logic here
        },
        closeAssignModal() {
            this.showAssignModal = false;
            this.assignStep = 1;
            this.assignIzinType = null;
            this.AssignDateRange = null;
            this.AssignTime = null;
            this.permissionReason = "";
            this.tempStartDate = null;
            this.tempEndDate = null;
            this.AssignFile = null;
        },
        isMobile() {
            return this.windowWidth < 768;
        },
        updateWindowWidth() {
            this.windowWidth = window.innerWidth;
        },
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },
        goToPage(page) {
            this.currentPage = page;
        },
        setViewMode(mode) {
            this.viewMode = mode;
            localStorage.setItem("preferredViewMode", mode);
        },
        showDetail(item) {
            this.selectedItem = item;
            this.showDetailModal = true;
        },
        showPicture(item) {
            this.selectedItem = item;
            this.showDetailModal = true;
        },
        showDeleteConfirm(id) {
            this.deleteItemId = id;
            this.showDeleteModal = true;
            this.showActionPopup = false;
            this.showDetailModal = false;
        },
        confirmDelete() {
            this.permissionRecords = this.permissionRecords.filter(
                (item) => item.No_Transaksi !== this.deleteItemId
            );
            this.showDeleteModal = false;
            ElNotification({
                title: "Berhasil",
                message: "Pengajuan izin berhasil dihapus",
                type: "success",
                position: "bottom-right",
            });
            if (
                this.selectedItem &&
                this.selectedItem.No_Transaksi === this.deleteItemId
            ) {
                this.selectedItem = null;
            }
        },
        showActionMenu(item, event) {
            this.selectedItem = item;
            if (event) {
                this.actionMenuPosition = {
                    top: `${event.clientY}px`,
                    left: `${event.clientX}px`,
                };
            } else {
                this.actionMenuPosition = {
                    top: "50%",
                    left: "50%",
                    transform: "translate(-50%, -50%)",
                };
            }
            this.showActionPopup = true;
        },
        editPermission(item) {
            this.showActionPopup = false;
            ElMessage.info("Fitur edit akan segera tersedia");
            // You would typically load the item's data into the assign modal for editing here
            // For example:
            // this.assignIzinType = item.Tipe_Izin;
            // this.AssignDateRange = [item.Tanggal_Mulai, item.Tanggal_Selesai];
            // this.permissionReason = item.Alasan;
            // this.AssignTime = item.Waktu ? parseFloat(item.Waktu.split('-')[0]) : null; // Parse time if available
            // this.showAssignModal = true;
        },
        hideSuggestions() {
            setTimeout(() => {
                this.showSearchSuggestions = false;
            }, 200);
        },
        selectSuggestion(suggestion) {
            this.searchQuery = suggestion;
            this.showSearchSuggestions = false;
        },
        truncateText(text, length) {
            return text.length > length
                ? text.substring(0, length) + "..."
                : text;
        },
        getTypeClass(type) {
            return (
                {
                    sakit: "type-sakit",
                    terlambat: "type-terlambat",
                    pulangCepat: "type-pulang-cepat",
                    "Tidak Masuk": "type-tidak-masuk",
                    cuti: "type-cuti-tahunan",
                    cutiHutang: "type-cuti-tahunan",
                    izinFull: "type-izin-pribadi",
                }[type] || ""
            );
        },
        getStatusClass(status) {
            if (status === "Y") {
                return "status-approved";
            } else if (status === null || status === undefined) {
                return "status-pending";
            } else if (status === "T") {
                return "status-rejected";
            }
            return "";
        },
        getStatusHeaderClass(status) {
            if (status === "Y") {
                return "header-approved";
            } else if (status === null || status === undefined) {
                return "header-pending";
            } else if (status === "T") {
                return "header-rejected";
            }
            return "";
        },
        checkScreenSize() {
            if (window.innerWidth >= 768) {
                this.viewMode = "table";
            } else {
                this.viewMode = "card";
            }
        },
    },
    mounted() {
        // Set day/night based on current time for the header icon
        const currentHour = new Date().getHours();
        this.dayNight =
            currentHour >= 6 && currentHour < 18 ? "Siang" : "Malam";

        // Check preferred view mode from localStorage or detect device
        const preferredMode = localStorage.getItem("preferredViewMode");
        if (preferredMode) {
            this.viewMode = preferredMode;
        } else {
            this.viewMode = this.isMobile() ? "card" : "table";
        }

        // Add event listener for window resize
        window.addEventListener("resize", () => {
            this.updateWindowWidth();
            if (!localStorage.getItem("preferredViewMode")) {
                this.viewMode = this.isMobile() ? "card" : "table";
            }
        });
        this.getData();
        this.checkScreenSize();
        // Simulate fetching data
    },
    unmounted() {
        window.removeEventListener("resize", this.updateWindowWidth);
    },
};
</script>

<style>
/* Consolidated and deduplicated styles */
*,
*::before,
*::after {
    --primary: #6366f1;
    --primary-dark: #4f46e5;
    --primary-light: #a5b4fc;
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
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.delete-modal-content {
    padding-right: 0 !important;
    padding-left: 0 !important;
}
/* detail */
/* overlay dialog */
.el-overlay-dialog {
    display: flex;
    justify-content: center;
    align-items: center;
    max-height: 100vh;
    max-width: 100vw;
    overflow: hidden;
}

.el-dialog {
    display: flex;
    margin: 0px;
    flex-direction: column; /* Tata header, body, footer secara vertikal */
    max-height: calc(
        100vh - 0px
    ); /* Sesuaikan Xpx untuk margin/padding di sekitar modal */
    /* Anda mungkin ingin menambahkan background-color dan box-shadow di sini */
}

.el-dialog__body {
    flex-grow: 1; /* Biarkan body mengisi sisa ruang yang tersedia */
    overflow-y: auto; /* *Ini Kunci Utamanya*: Biarkan body modal discroll jika kontennya terlalu panjang */
    padding: 1rem; /* Atur padding sesuai kebutuhan */
}

.el-dialog__header,
.el-dialog__footer {
    flex-shrink: 0; /* Pastikan header/footer tidak mengecil */
}
/* akhir dialog */
/* Modal container */
.detail-modal {
    border-radius: 8px;
}

.detail-modal .el-dialog__header {
    border-bottom: 1px solid #eee;
    padding: 16px 20px;
}

.detail-modal .el-dialog__body {
    padding: 20px;
}
/* Tooltip container adjustment */
.el-popper {
    border: none !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
    border-radius: 8px !important;
    padding: 0 !important;
    background: white !important;
}

.el-popper__arrow {
    display: none !important;
}

.el-tooltip__popper.is-dark {
    background: transparent !important;
    border: none !important;
}

/* Grid layout for details */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    margin-bottom: 8px;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-weight: 500;
    color: #606266;
    margin-bottom: 4px;
    font-size: 14px;
}

.detail-value {
    color: #303133;
    font-size: 15px;
}

.detail-value.highlight {
    font-weight: 600;
    color: #409eff;
}

.detail-value.multiline {
    white-space: pre-line;
    line-height: 1.6;
}

.empty-value {
    color: #909399;
    font-style: italic;
}

/* Approval progress */
.approval-progress {
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px dashed #eaeaea;
}

.progress-title {
    font-weight: 500;
    margin-bottom: 12px;
    color: #606266;
}

/* Footer buttons */
.modal-footer-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 12px 20px;
    border-top: 1px solid #eee;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }

    .detail-modal .el-dialog {
        margin: 0;
        max-height: 100vh;
    }
}

/*  */

.el-popper {
    border: none !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
    border-radius: 8px !important;
    padding: 0 !important;
    background: white !important;
}

.el-popper__arrow {
    display: none !important;
}

.el-tooltip__popper.is-dark {
    background: transparent !important;
    border: none !important;
}

.buttonAccept {
    width: 100%;
    padding: 0.3rem 1rem;
    text-align: center;
    transition: all 0.3s ease;
    background: var(--surface-soft);
}

.buttonAccept.left {
    border-radius: 0px 20px 20px 0px;
}
.buttonAccept.right {
    border-radius: 20px 0px 0px 20px;
}

.buttonAccept:hover {
    background: var(--primary);
    color: var(--surface) !important;
}

.btn-modern {
    display: flex;
    align-items: center;
    padding: 0.75rem 1.2rem;
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
.animated-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.attachment-link.is-loading {
    pointer-events: none;
    opacity: 0.7;
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

.btn-modern:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.btn-modern:disabled:hover {
    transform: none !important;
    box-shadow: none !important;
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

.btn-danger {
    background: var(--danger);
    color: white;
}
.btn-danger:hover {
    background: #c23030;
    transform: translateY(-1px);
    box-shadow: var(--shadow);
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
        rgba(255, 255, 255, 0.6),
        transparent
    );
    transition: left 0.5s;
}

.shiningEffect:hover::before {
    left: 100%;
}

/* Header */
.modern-header {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
    min-height: 140px;
}

.header-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--gradient-primary);
    opacity: 0.95;
}

.header-background::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><circle cx="30" cy="30" r="2"/></g></svg>');
    animation: float 20s ease-in-out infinite;
}

.header-content {
    position: relative;
    z-index: 2;
    padding: 2.5rem;
    color: white;
}

.header-info {
    display: flex;
    align-items: center;
    justify-content: start;
    gap: 1.5rem;
}

.icon-container {
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-container:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.icon-container i {
    font-size: 2.2rem;
    z-index: 2;
}

.title {
    font-size: 2rem;
    font-weight: 400;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    letter-spacing: -0.025em;
}

.subtitle {
    text-align: start;
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
    font-size: 1.1rem;
    font-weight: 400;
}

.header-actions {
    height: 100%;
}

.date-range {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.date-item {
    text-align: center;
    color: white;
}

/* Control Panel (Search, Filter, View Toggle) */
.control-panel {
    background: var(--surface);
    border-radius: 20px;
    padding: 0.5rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

.search-filter-section {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-container {
    position: relative;
    flex: 1;
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

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--surface);
    border-radius: 0 0 12px 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 100;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid var(--border);
    border-top: none;
}
.suggestion-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.suggestion-item:hover {
    background: var(--surface-soft);
}

.filters-container {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.filter-dropdown {
    position: relative;
}

.filter-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 0.5rem;
    background: var(--surface-soft);
    border: 1px solid var(--border);
    border-radius: 12px;
    color: var(--text);
    cursor: pointer;
    transition: all 0.3s ease;
    justify-content: space-between;
}

.filter-btn:hover {
    background: var(--surface);
    border-color: var(--primary);
}

.view-toggle {
    display: flex;
    background: var(--surface-soft);
    border-radius: 10px;
    padding: 0.25rem;
}

.toggle-btn {
    padding: 0.5rem;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--text-muted);
}

.toggle-btn.active {
    background: var(--primary);
    color: white;
}

.toggle-btn:hover:not(.active) {
    background: var(--surface);
    color: var(--text);
}

/* Content Container & Loading/Empty States */
.content-container {
    background: var(--surface);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--border);
}

.loading-container,
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
    min-height: 300px;
}

.loading-animation {
    position: relative;
    margin-bottom: 1.5rem;
}

.loading-dots {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1rem;
}

.loading-dots span {
    width: 8px;
    height: 8px;
    background: var(--primary);
    border-radius: 50%;
    animation: bounce 1.4s ease-in-out infinite both;
}

.loading-dots span:nth-child(1) {
    animation-delay: -0.32s;
}
.loading-dots span:nth-child(2) {
    animation-delay: -0.16s;
}

.loading-text {
    color: var(--text-muted);
    font-size: 1.1rem;
    margin: 0;
}

.empty-icon {
    font-size: 3rem;
    color: var(--border);
    margin-bottom: 1rem;
    opacity: 0.7;
}
.empty-state h4 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: var(--text);
}
.empty-state p {
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}

/* Table View */
.table-view-container {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
.table-responsive-wrapper {
    min-width: 800px;
}
.modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.875rem;
}
.modern-table th,
.modern-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border);
}
.modern-table th {
    position: sticky;
    top: 0;
    background-color: var(--surface-soft);
    z-index: 10;
    font-weight: 600;
    color: var(--text);
}
.header-cell {
    white-space: nowrap;
}
.header-cell-content {
    display: flex;
    align-items: center;
    justify-content: center;
}
.badge-count {
    margin-left: 0.5rem;
    background: var(--primary);
    color: white;
    border-radius: 10px;
    padding: 0.15rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
}
.sticky-column {
    position: sticky;
    left: 0;
    background: var(--surface) !important;
    z-index: 11;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
}
.table-row {
    transition: background-color 0.2s ease;
    cursor: pointer;
}
.table-row:hover {
    background-color: var(--surface-soft);
}
.transaction-cell {
    display: flex;
    flex-direction: column;
}
.transaction-id {
    font-weight: 500;
    color: var(--text);
}
.transaction-type {
    font-size: 0.75rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    margin-top: 0.25rem;
}
.type-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 0.5rem;
}
.type-sakit .type-indicator {
    background: var(--danger);
}
.type-terlambat .type-indicator {
    background: var(--warning);
}
.type-pulang-cepat .type-indicator {
    background: var(--info);
}
.type-tidak-masuk .type-indicator,
.type-cuti-tahunan .type-indicator,
.type-izin-pribadi .type-indicator {
    background: var(--primary);
}

.date-cell {
    white-space: nowrap;
    width: 100%;
}
.reason-cell {
    max-width: 200px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.attachment-cell {
    display: flex;
    align-items: center;
    justify-content: start;
}
.attachment-link {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: var(--primary);
    text-decoration: none;
}
.attachment-link:hover {
    text-decoration: underline;
}
.no-attachment {
    color: var(--text-muted);
}
.status-cell {
    display: flex;
    justify-content: center;
}
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.status-approved {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}
.status-pending {
    background: rgba(6, 182, 212, 0.1);
    color: var(--info);
}
.status-rejected {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}

.action-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}
.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-action:hover {
    background: var(--surface-soft);
}
.btn-action.delete-btn {
    color: var(--danger);
}
.btn-action.delete-btn:hover {
    background: rgba(239, 68, 68, 0.1);
}
.btn-action.more-btn {
    color: var(--text-muted);
}
.btn-action.more-btn:hover {
    color: var(--text);
}

/* Card View */
.card-view-container {
    padding: 1rem;
}
.card-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.25rem;
}
.permission-card {
    background: var(--surface);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid var(--border);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}
.permission-card:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}
.card-header {
    padding: 1rem;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.header-approved {
    background: linear-gradient(135deg, #34d399 0%, #059669ce 100%);

    /* background: linear-gradient(135deg, #a78bfa 0%, #8a5cf6ce 100%); */
}
.header-pending {
    /* background: linear-gradient(135deg, #34d399 0%, #059669ce 100%); */
    background: linear-gradient(135deg, #818cf8 0%, #6365f1ce 100%);
}
.header-rejected {
    background: linear-gradient(135deg, #f472b5 0%, #ec489ad3 100%);
}
.card-date {
    font-weight: 500;
    font-size: 0.8rem;
}
.card-status {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
}
.card-body {
    padding: 1rem;
    flex-grow: 1;
}
.card-row {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
}
.card-row i {
    color: var(--primary);
    margin-top: 0.1rem;
    flex-shrink: 0;
}
.card-label {
    color: var(--text-muted);
    flex-shrink: 0;
    width: 80px;
}
.card-value {
    color: var(--text);
    word-break: break-word;
}
.card-link {
    color: var(--primary);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.card-link:hover {
    text-decoration: underline;
}
.no-file {
    color: var(--text-muted);
    font-style: italic;
}
.card-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid var(--border);
    display: flex;
    /* justify-content: space-between; */
    justify-content: end;
    gap: 0.5rem;
}
.card-btn {
    padding: 0.5rem 1rem;
    border-radius: 8px;
    border: none;
    font-size: 0.8rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.card-btn.delete-btn {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger);
}
.card-btn.delete-btn:hover {
    background: rgba(239, 68, 68, 0.2);
}
.card-btn.more-btn {
    background: var(--surface-soft);
    color: var(--text-muted);
}
.card-btn.more-btn:hover {
    background: var(--border);
    color: var(--text);
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1.5rem 0;
    border-top: 1px solid var(--border);
}
.page-btn {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--surface);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 0 0.25rem;
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
.page-numbers {
    display: flex;
    margin: 0 0.5rem;
}
.page-number {
    min-width: 36px;
    height: 36px;
    padding: 0 0.5rem;
    border-radius: 8px;
    border: 1px solid transparent;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 0 0.25rem;
    font-weight: 500;
}
.page-number:hover {
    border-color: var(--border);
}
.page-number.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}
.page-info {
    margin: 0 1rem;
    font-size: 0.875rem;
    color: var(--text-muted);
}

/* Detail Modal */
.detail-modal-content {
    padding: 0.5rem;
}
.detail-section {
    margin-bottom: 1.5rem;
}
.detail-row {
    display: flex;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}
.detail-label {
    width: 120px;
    font-weight: 500;
    color: var(--text-muted);
}
.detail-value {
    flex: 1;
    color: var(--text);
}
.detail-link {
    color: var(--primary);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.detail-link:hover {
    text-decoration: underline;
}
.detail-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

/* Delete Confirmation Modal */
.delete-modal-content {
    text-align: center;
    padding: 1rem;
}
.warning-icon {
    font-size: 3rem;
    color: var(--danger);
    margin-bottom: 1rem;
}
.success-icon {
    font-size: 3rem;
    color: var(--success);
    margin-bottom: 1rem;
}
.delete-modal-footer {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

/* Action Menu */
.action-menu {
    position: fixed;
    z-index: 2000;
    background: var(--surface);
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    min-width: 180px;
    overflow: hidden;
    animation: fadeInUp 0.2s ease;
}
.action-menu-item {
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
}
.action-menu-item:hover {
    background: var(--surface-soft);
}
.action-menu-item.text-danger {
    color: var(--danger);
}
.action-menu-item.text-danger:hover {
    background: rgba(239, 68, 68, 0.1);
}
.action-menu-divider {
    height: 1px;
    background: var(--border);
    margin: 0.25rem 0;
}
.action-menu-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1999;
    background: rgba(0, 0, 0, 0.3);
}

/* Assign Modal (New/Edit Permission) */
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
    z-index: 1048;
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
    display: flex;
    flex-direction: column;
}

.modal-container.assign-modal {
    max-width: 700px;
    max-height: 95vh;
}

.modal-overlay.show .modal-container {
    transform: scale(1) translateY(0);
}
.section-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem 2rem;
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
    flex: 1;
}
.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid var(--border);
    display: flex;
    gap: 1rem;
    justify-content: end;
}
.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #eee;
    background-color: #f8f9fa;
    border-bottom-left-radius: 12px;
    border-bottom-right-radius: 12px;
}
.footer-actions {
    display: flex;
    gap: 0.5rem;
}
.form-section {
    padding: 0.2rem;
}

.form-group {
    margin-bottom: 1.5rem; /* Standard margin for form groups */
    border: 1px solid var(--border); /* Added for visual separation */
    background: rgba(99, 102, 241, 0.05); /* Soft background */
    border-radius: 12px;
    padding: 1rem;
    overflow-y: auto; /* Ensure content within is scrollable if needed */
    max-height: 18rem; /* Cap height to prevent excessive modal height */
}

.form-group.AssignRingkasan {
    max-height: 25rem;
}

.form-group label,
.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text);
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
    transform: translateY(-1px);
}

.permission-type-options {
    margin-bottom: 0rem;
}

.permission-type-card {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
    background-color: var(--surface);
}

.permission-type-card:hover {
    border-color: #adb5bd;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.permission-type-card.selected {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.permission-type-icon {
    font-size: 1.5rem;
    color: #0d6efd;
    margin-bottom: 0.75rem;
}

.permission-type-content h6 {
    font-size: 0.9375rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.permission-type-check {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    font-size: 1rem;
    color: #adb5bd;
}

.permission-type-check .bi-check-circle-fill {
    color: #0d6efd;
}

/* File Upload */
.file-upload-wrapper {
    position: relative;
}
.file-upload-area {
    cursor: pointer;
    border: 1px dashed var(--border);
    border-radius: 0.375rem;
    padding: 1rem;
    min-height: 10rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    background-color: var(--surface);
}

.file-upload-area:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.file-upload-label {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 0.5rem;
    color: var(--text-muted);
    font-weight: 500;
    cursor: pointer;
    width: 100%;
    height: 100%;
}

.file-upload-label i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.file-display-area {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border);
    border-radius: 12px;
    background-color: var(--surface);
    color: var(--text);
    font-size: 0.95rem;
    min-height: fit-content;
}

.file-name {
    flex-grow: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-right: 1rem;
}

.btn-remove-file {
    background: none;
    border: none;
    color: var(--danger);
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: 50%;
    transition: background-color 0.2s ease;
}

.btn-remove-file:hover {
    background-color: rgba(239, 68, 68, 0.1);
}

/* Calendar Modals (Element Plus specific overrides) */
.el-overlay {
    z-index: 1050;
}
.el-dialog__body {
    padding-top: 10px;
    padding-bottom: 10px;
}
.el-calendar-table .el-calendar-day {
    padding: 2px;
    height: 50px;
}
.cell-date-wrapper {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    z-index: 1;
}
.date-table-cell {
    width: 100%;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    cursor: pointer;
    transition: background-color 0.2s, color 0.2s;
    position: relative;
    z-index: 2;
}
.date-cell {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    cursor: pointer;
    transition: background-color 0.2s, color 0.2s;
    position: relative;
    z-index: 2;
}
.cell-date-wrapper:hover .date-cell {
    background-color: #d9ecff;
}
.is-start .date-cell,
.is-end .date-cell {
    background-color: #409eff;
    color: white;
}
.is-in-range::before {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: #ecf5ff;
    z-index: 0;
}
.is-start::before {
    left: 50%;
}
.is-end::before {
    right: 50%;
}
.is-disabled-custom {
    color: #c0c4cc !important;
    cursor: not-allowed !important;
    background-color: #f5f7fa !important;
    pointer-events: none;
}
.is-disabled-custom .date-cell {
    background-color: transparent !important;
}
.is-disabled-custom:hover .date-cell {
    background-color: transparent !important;
}

/* Transitions */
.fade-in {
    animation: fadeIn 0.5s ease-in-out;
}
.fade-switch-enter-active,
.fade-switch-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-switch-enter-from,
.fade-switch-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
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
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

/* Responsive Adjustments */
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
        width: 100%;
        padding-bottom: 0.6rem;
        gap: 1rem;
    }
    .el-dialog.is-align-center {
        margin: auto 1rem;
    }
    .el-calendar__header {
        padding: 1px 0.2rem;
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
    .search-filter-section {
        align-items: stretch;
    }
    .filters-container {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .card-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    }
    .detail-row {
        flex-direction: column;
        gap: 0.25rem;
    }
    .detail-label {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .modern-header {
        min-height: 100px;
    }
    .header-content {
        padding: 1rem;
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
    .search-container {
        min-width: auto;
    }
    .modern-table {
        font-size: 0.8rem;
    }
    .sticky-column {
        min-width: 150px;
        position: relative;
        left: auto;
        z-index: 100;
        box-shadow: none;
    }
    .modal-container {
        margin: 0.5rem;
        max-height: 83vh;
    }
    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 1rem;
    }
    .modal-footer .btn-modern {
        width: 100%;
        justify-content: center;
    }
    .card-grid {
        grid-template-columns: 1fr;
    }
    .pagination-container {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .page-numbers {
        order: 1;
        width: 100%;
        justify-content: center;
        margin: 0.5rem 0;
    }
}

@media (max-width: 576px) {
    .header-content {
        padding: 1rem;
    }
    .title {
        font-size: 1.1rem;
    }
    .subtitle {
        font-size: 0.85rem;
    }
    .control-panel {
        padding: 0.75rem;
        margin-bottom: 0.5rem;
    }
    .search-filter-section {
        gap: 0.75rem;
    }
    .table-responsive-wrapper {
        border-radius: 12px;
    }
    .card-view-container {
        padding: 0.75rem;
    }
    .card-row {
        flex-direction: column;
        gap: 0.25rem;
    }
    .card-label {
        width: 100%;
    }
    .modal-container.assign-modal {
        width: 98%;
        max-height: 90vh;
    }
    .form-section {
        padding: 0.2rem;
    }
    .form-group {
        padding: 0.8rem;
    }
    .permission-type-card {
        padding: 0.75rem;
    }
    .permission-type-icon {
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
    }
    .permission-type-content h6 {
        font-size: 0.7rem;
    }
    .page-number,
    .page-btn {
        min-width: 30px;
        height: 30px;
        margin: 0 0.1rem;
    }
    .header-actions {
        flex-direction: column;
        width: 100%;
    }
    .date-range,
    .btn-modern {
        width: 100%;
        justify-content: center;
    }
}
</style>

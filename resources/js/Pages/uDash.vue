<template>
    <div class="container-fluid hris-dashboard">
        <!-- Header Section -->
        <div v-if="!loadingTop" class="row mb-4 gap-2 gap-md-0">
            <div class="col-md-6 col-12 d-none d-md-block">
                <div class="row m-0 dashboard-header">
                    <div class="col-md-6">
                        <h2 class="mb-0">Employee Dashboard</h2>
                        <p class="text-muted mb-0">
                            Halo,
                            {{
                                capitalize(
                                    namaKaryawan
                                        ? namaKaryawan
                                              .split(" ")
                                              .slice(0, 2)
                                              .join(" ")
                                        : "undefined"
                                )
                            }}
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="current-date">
                            <span class="date">{{ currentDate }}</span>
                            <span class="time">{{ currentTime }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div
                    class="content-container text-white p-3 h-100 d-flex justify-content-center align-items-center flex-column"
                >
                    <span class="fs-6">{{
                        `Hari ini SHIFT ${shiftHariIni}`
                    }}</span>
                    <div
                        class="d-flex justify-content-evenly w-100 align-items-center"
                    >
                        <span class="fs-1 fw-bold">{{ Jam_Masuk }} </span>
                        <span><i class="bi bi-three-dots fs-3"></i></span>
                        <span class="fs-1 fw-bold">{{ Jam_Keluar }}</span>
                    </div>
                    <span
                        class="btn-modern btn-primary px-2 flex-shrink-1 d-flex align-items-center w-100 text-center justify-content-center rounded-4 py-2 gradientPrimary"
                        ><i
                            class="bi bi-clock-history me-2 d-flex justify-content-center align-items-center"
                        ></i>
                        Besok Shift {{ shiftBesok }}
                    </span>
                </div>
            </div>
        </div>
        <!-- Header Section -->

        <div v-else class="row mb-4 gap-2 gap-md-0">
            <div class="col-md-6 col-12 d-none d-md-block">
                <div class="row m-0 dashboard-header skeleton-container">
                    <div class="col-md-6">
                        <div
                            class="skeleton-text"
                            style="width: 60%; height: 28px; margin-bottom: 8px"
                        ></div>
                        <div
                            class="skeleton-text"
                            style="width: 80%; height: 16px"
                        ></div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="current-date">
                            <div
                                class="skeleton-text"
                                style="
                                    width: 120px;
                                    height: 20px;
                                    display: inline-block;
                                    margin-right: 8px;
                                "
                            ></div>
                            <div
                                class="skeleton-text"
                                style="
                                    width: 80px;
                                    height: 20px;
                                    display: inline-block;
                                "
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div
                    class="content-container text-white p-3 h-100 skeleton-container d-flex justify-content-center align-items-center flex-column"
                >
                    <div
                        class="skeleton-text"
                        style="width: 40%; height: 18px; margin-bottom: 16px"
                    ></div>
                    <div
                        class="d-flex justify-content-evenly w-100 align-items-center mb-3"
                    >
                        <div
                            class="skeleton-text"
                            style="width: 25%; height: 48px"
                        ></div>
                        <div
                            class="skeleton-text"
                            style="width: 24px; height: 24px"
                        ></div>
                        <div
                            class="skeleton-text"
                            style="width: 25%; height: 48px"
                        ></div>
                    </div>
                    <div
                        class="skeleton-button"
                        style="width: 100%; height: 40px; border-radius: 16px"
                    ></div>
                </div>
            </div>
        </div>
        <!-- Quick Stats Section -->
        <div
            v-if="!loadingQuick"
            class="row d-flex justify-content-center mb-3 fade-in align-items-center"
        >
            <div class="col-md-12">
                <div class="leave-info-panel fade-in">
                    <div class="container-fluid px-0">
                        <div class="row g-2 g-md-3 justify-content-center">
                            <!-- Hutang Cuti -->
                            <div class="col-4">
                                <div
                                    class="info-card bg-white rounded shadow-sm p-3 h-100 position-relative"
                                >
                                    <div
                                        class="d-flex align-items-start h-100 flex-column flex-md-row justify-content-start justify-content-md-start w-100"
                                    >
                                        <div
                                            class="icon-circle bg-danger-light me-2"
                                        >
                                            <i
                                                class="fas fa-exclamation-triangle text-danger"
                                            ></i>
                                        </div>
                                        <div>
                                            <div
                                                class="info-value text-dark fw-bold fs-4"
                                            >
                                                {{ totalTerlambat?.toFixed(0)
                                                }}<span
                                                    class="p-1"
                                                    style="
                                                        font-size: 0.6rem;
                                                        opacity: 0.5;
                                                    "
                                                    >menit</span
                                                >
                                            </div>
                                            <div
                                                class="info-label text-muted small"
                                            >
                                                Total terlambat
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Sisa Cuti -->
                            <div class="col-4">
                                <div
                                    class="info-card bg-white rounded shadow-sm p-3 h-100 position-relative"
                                >
                                    <div
                                        class="d-flex align-items-start h-100 flex-column flex-md-row justify-content-start justify-content-md-start w-100"
                                    >
                                        <div
                                            style=""
                                            class="icon-circle bg-warning-light me-md-3"
                                        >
                                            <i
                                                class="fas fa-clock text-warning"
                                            ></i>
                                        </div>
                                        <div>
                                            <div
                                                class="info-value text-dark fw-bold fs-4"
                                            >
                                                {{ totalLembur?.toFixed(0)
                                                }}<span
                                                    class="p-1"
                                                    style="
                                                        font-size: 0.6rem;
                                                        opacity: 0.5;
                                                    "
                                                    >jam</span
                                                >
                                            </div>
                                            <div
                                                class="info-label text-muted small"
                                            >
                                                Total Lembur
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Menunggu Persetujuan -->
                            <div class="col-4">
                                <div
                                    class="info-card bg-white rounded shadow-sm p-3 h-100 position-relative"
                                >
                                    <div
                                        class="d-flex align-items-start h-100 flex-column flex-md-row justify-content-start justify-content-md-start w-100"
                                    >
                                        <div
                                            class="icon-circle bg-primary-light me-3"
                                        >
                                            <i
                                                class="fas fa-calendar-check text-primary"
                                            ></i>
                                        </div>
                                        <div>
                                            <div
                                                class="info-value text-dark fw-bold fs-4"
                                            >
                                                {{ sisaCuti }}
                                            </div>
                                            <div
                                                class="info-label text-muted small"
                                            >
                                                Sisa Cuti
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div
            v-else
            class="row d-flex justify-content-center mb-3 align-items-center"
        >
            <div class="col-md-12">
                <div class="leave-info-panel skeleton-container">
                    <div class="container-fluid px-0">
                        <div class="row g-2 g-md-3 justify-content-center">
                            <!-- Terlambat -->
                            <div class="col-4">
                                <div
                                    class="info-card bg-white rounded shadow-sm p-3 h-100 position-relative"
                                >
                                    <div
                                        class="d-flex align-items-start h-100 flex-column flex-md-row justify-content-start justify-content-md-start w-100"
                                    >
                                        <div
                                            class="skeleton-circle"
                                            style="
                                                width: 40px;
                                                height: 40px;
                                                border-radius: 50%;
                                                margin-right: 12px;
                                            "
                                        ></div>
                                        <div style="flex: 1">
                                            <div
                                                class="skeleton-text"
                                                style="
                                                    width: 60%;
                                                    height: 28px;
                                                    margin-bottom: 4px;
                                                "
                                            ></div>
                                            <div
                                                class="skeleton-text"
                                                style="width: 90%; height: 14px"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lembur -->
                            <div class="col-4">
                                <div
                                    class="info-card bg-white rounded shadow-sm p-3 h-100 position-relative"
                                >
                                    <div
                                        class="d-flex align-items-start h-100 flex-column flex-md-row justify-content-start justify-content-md-start w-100"
                                    >
                                        <div
                                            class="skeleton-circle"
                                            style="
                                                width: 40px;
                                                height: 40px;
                                                border-radius: 50%;
                                                margin-right: 12px;
                                            "
                                        ></div>
                                        <div style="flex: 1">
                                            <div
                                                class="skeleton-text"
                                                style="
                                                    width: 60%;
                                                    height: 28px;
                                                    margin-bottom: 4px;
                                                "
                                            ></div>
                                            <div
                                                class="skeleton-text"
                                                style="width: 90%; height: 14px"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sisa Cuti -->
                            <div class="col-4">
                                <div
                                    class="info-card bg-white rounded shadow-sm p-3 h-100 position-relative"
                                >
                                    <div
                                        class="d-flex align-items-start h-100 flex-column flex-md-row justify-content-start justify-content-md-start w-100"
                                    >
                                        <div
                                            class="skeleton-circle"
                                            style="
                                                width: 40px;
                                                height: 40px;
                                                border-radius: 50%;
                                                margin-right: 12px;
                                            "
                                        ></div>
                                        <div style="flex: 1">
                                            <div
                                                class="skeleton-text"
                                                style="
                                                    width: 60%;
                                                    height: 28px;
                                                    margin-bottom: 4px;
                                                "
                                            ></div>
                                            <div
                                                class="skeleton-text"
                                                style="width: 90%; height: 14px"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Main Content Section -->
        <div class="row flex-row-reverse">
            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Calendar -->
                <div v-if="!loadingCalendar" class="">
                    <div class="position-relative wrapper-calendar">
                        <div
                            class="cardFloat position-absolute d-flex justify-content-between align-items-center"
                        >
                            <div
                                class="fw-bold text-white d-flex align-items-center justify-content-center h-100"
                            >
                                <!-- <i
                                    class="bg-white text-black p-3 border-primary rounded-4 me-2 bi bi-cloud-sun d-flex justify-content-center align-items-center"
                                ></i> -->
                                {{
                                    getJustMonthAndYear(
                                        dataDateAbsen[0]?.Tanggal
                                    )
                                }}
                            </div>
                            <div class="d-flex gap-2">
                                <button
                                    @click="prevWeekAbsensi"
                                    class="btn-modern btn-primary p-1"
                                    style="border-radius: 8px !important"
                                >
                                    <i
                                        class="bi bi-arrow-left d-flex justify-content-center align-items-center"
                                    ></i>
                                </button>
                                <button
                                    @click="nextWeekAbsensi"
                                    class="btn-modern btn-primary p-1"
                                    style="border-radius: 8px !important"
                                >
                                    <i
                                        class="bi bi-arrow-right d-flex justify-content-center align-items-center"
                                    ></i>
                                </button>
                            </div>
                        </div>
                        <div class="cardIn position-absolute">
                            <div class="p-3 d-flex gap-2">
                                <div
                                    class="w-100"
                                    v-for="(data, idx) in dataDateAbsen"
                                    :key="idx"
                                >
                                    <el-tooltip
                                        :content="getTooltipContent(data)"
                                        raw-content
                                        placement="bottom"
                                        :width="350"
                                    >
                                        <div class="text-center w-100">
                                            <div
                                                class="btn-modern btn-primary justify-content-center tanggalBar p-2"
                                                :class="getClassAbsensi(data)"
                                            >
                                                {{ getJustDate(data.Tanggal) }}
                                            </div>
                                            {{ getJustDay(data.Tanggal) }}
                                        </div>
                                    </el-tooltip>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="">
                    <div class="position-relative wrapper-calendar">
                        <!-- Card Float Skeleton -->
                        <div
                            class="cardFloat position-absolute d-flex justify-content-between align-items-center skeleton-container"
                        >
                            <div
                                class="fw-bold text-white d-flex align-items-center justify-content-center h-100"
                            >
                                <div
                                    class="skeleton-text"
                                    style="width: 120px; height: 24px"
                                ></div>
                            </div>
                            <div class="d-flex gap-2">
                                <div
                                    class="skeleton-button"
                                    style="
                                        width: 32px;
                                        height: 32px;
                                        border-radius: 8px;
                                    "
                                ></div>
                                <div
                                    class="skeleton-button"
                                    style="
                                        width: 32px;
                                        height: 32px;
                                        border-radius: 8px;
                                    "
                                ></div>
                            </div>
                        </div>

                        <!-- Card In Skeleton -->
                        <div
                            class="cardIn position-absolute skeleton-container"
                        >
                            <div class="p-3 d-flex gap-2">
                                <div class="w-100" v-for="i in 7" :key="i">
                                    <div class="text-center w-100">
                                        <div
                                            class="skeleton-date"
                                            style="
                                                width: 100%;
                                                height: 40px;
                                                border-radius: 8px;
                                                margin-bottom: 8px;
                                            "
                                        ></div>
                                        <div
                                            class="skeleton-text"
                                            style="
                                                width: 60%;
                                                height: 16px;
                                                margin: 0 auto;
                                            "
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="!loadingCard" class="p-3 px-md-3 px-4 mb-md-0">
                    <div class="row justify-content-center w-100">
                        <div
                            class="col-md-4 col-4 mb-3 px-1 px-md-1"
                            v-for="action in filteredQuickActions"
                            :key="action.id"
                        >
                            <a
                                :href="action.url"
                                class="btn btn-action"
                                :class="action.class"
                            >
                                <i
                                    :class="action.icon"
                                    style="margin-bottom: 0.7rem"
                                ></i>
                                <span>{{ action.label }}</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div v-else class="p-3 px-md-5 px-4 mb-md-3 skeleton-container">
                    <div class="row justify-content-center">
                        <!-- Repeat for each quick action (typically 3-6 items) -->
                        <div
                            class="col-md-4 col-4 mb-3 px-1 px-md-1"
                            v-for="i in 6"
                            :key="i"
                        >
                            <div class="btn-action-skeleton">
                                <div
                                    class="skeleton-icon"
                                    style="
                                        width: 24px;
                                        height: 24px;
                                        margin: 0 auto 0.7rem;
                                        border-radius: 50%;
                                    "
                                ></div>
                                <div
                                    class="skeleton-text"
                                    style="
                                        width: 80%;
                                        height: 16px;
                                        margin: 0 auto;
                                    "
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- approver izin -->
                <div class="">
                    <div class="card leave-requests-card">
                        <div
                            class="card-header d-flex justify-content-between align-items-center"
                        >
                            <h5 class="mb-0">Approver izin</h5>
                        </div>
                        <div class="card-body" style="max-height: 13rem">
                            <div
                                v-for="(data, idx) in nama_Approver"
                                :key="idx"
                                class="row fs-6 mt-3 ps-3 align-items-center justify-content-between"
                            >
                                <div
                                    v-tooltip="data.Nama"
                                    style="font-size: 0.9rem !important"
                                    class="col-6 p-0 d-flex justify-content-start text-start align-items-center"
                                >
                                    {{
                                        truncateText(capitalize(data?.Nama), 30)
                                    }}
                                </div>

                                <div
                                    style="font-size: 0.7rem !important"
                                    class="col-6 p-0 d-flex justify-content-end px-2 align-items-center"
                                >
                                    <span
                                        class=""
                                        :class="
                                            data.Status == 'Disetujui'
                                                ? 'badge-lembur-selesai'
                                                : 'badge-lembur'
                                        "
                                    >
                                        Approver {{ data.order_flow }}
                                    </span>
                                </div>
                            </div>

                            <!-- <button
                                class="btn btn-primary w-100 mt-3"
                                @click="newLeaveRequest"
                            >
                                <i class="bi bi-plus-circle"></i> Buat Pengajuan
                                Baru
                            </button> -->
                        </div>
                    </div>
                </div>

                <div class="">
                    <div class="card leave-requests-card">
                        <div
                            class="card-header d-flex justify-content-between align-items-center"
                        >
                            <h5 class="mb-0">Anggota Team</h5>
                            <div class="d-flex gap-2">
                                <button
                                    @click="prevPageAnggota"
                                    class="btn-modern btn-primary p-1"
                                    style="border-radius: 8px !important"
                                >
                                    <i
                                        class="bi bi-arrow-left d-flex justify-content-center align-items-center"
                                    ></i>
                                </button>
                                <span
                                    >{{ currentPageAnggota }}
                                    of
                                    {{ totalPagesAnggota }}</span
                                >
                                <button
                                    @click="nextPageAnggota"
                                    class="btn-modern btn-primary p-1"
                                    style="border-radius: 8px !important"
                                >
                                    <i
                                        class="bi bi-arrow-right d-flex justify-content-center align-items-center"
                                    ></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body" style="height: 13rem">
                            <div
                                v-for="(data, idx) in paginatedAnggota"
                                :key="idx"
                                class="row fs-6 mt-3 ps-3"
                            >
                                <div
                                    v-tooltip="data.Nama"
                                    style="font-size: 0.9rem !important"
                                    class="col-4 p-0 d-flex justify-content-start text-start align-items-center"
                                >
                                    {{
                                        truncateText(capitalize(data?.Nama), 10)
                                    }}
                                </div>
                                <div
                                    v-tooltip="data.divisi"
                                    style="
                                        font-size: 0.9rem !important;
                                        cursor: pointer;
                                    "
                                    class="col-4 p-0 d-flex justify-content-center align-items-center"
                                >
                                    {{ truncateText(data.divisi, 10) }}
                                </div>
                                <div
                                    v-tooltip="
                                        data.HP == '-'
                                            ? 'silahkan hubungi admin untuk mengubah No HP'
                                            : ''
                                    "
                                    style="font-size: 0.7rem !important"
                                    class="col-4 p-0 d-flex justify-content-center align-items-center"
                                >
                                    <span
                                        class=""
                                        :class="
                                            data.Status == 'Disetujui'
                                                ? 'badge-lembur-selesai'
                                                : 'badge-lembur'
                                        "
                                    >
                                        {{ data.HP }}
                                    </span>
                                </div>
                            </div>

                            <!-- <button
                                class="btn btn-primary w-100 mt-3"
                                @click="newLeaveRequest"
                            >
                                <i class="bi bi-plus-circle"></i> Buat Pengajuan
                                Baru
                            </button> -->
                        </div>
                    </div>
                </div>

                <!-- <div class="card holiday-card mb-4">
                    <div
                        class="card-header d-flex justify-content-between align-items-center"
                    >
                        <h5 class="mb-0">Holiday Calendar</h5>
                        <button
                            class="btn btn-sm btn-outline-primary"
                            @click="viewAllHolidays"
                        >
                            View All
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="upcoming-holiday" v-if="upcomingHoliday">
                            <div class="holiday-date">
                                {{ upcomingHoliday.date }}
                            </div>
                            <div class="holiday-name">
                                {{ upcomingHoliday.name }}
                            </div>
                            <div class="holiday-days">
                                {{ upcomingHoliday.daysLeft }} days left
                            </div>
                        </div>
                        <div class="holiday-list">
                            <div
                                class="holiday-item"
                                v-for="holiday in nextHolidays"
                                :key="holiday.id"
                            >
                                <div class="holiday-item-date">
                                    {{ holiday.date }}
                                </div>
                                <div class="holiday-item-name">
                                    {{ holiday.name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>
            <!-- Left Column -->
            <div class="col-lg-8 mb-1">
                <div class="row justify-content-between">
                    <!-- Leave Requests -->
                    <div class="col-md-6">
                        <div v-if="!loadingLembur" class="">
                            <div class="card leave-requests-card">
                                <div
                                    class="card-header d-flex justify-content-between align-items-center"
                                >
                                    <h5 class="mb-0">Riwayat Lembur</h5>
                                    <div class="d-flex gap-2">
                                        <button
                                            :disabled="
                                                paginationLembur.current_page <=
                                                1
                                            "
                                            @click="
                                                getAllLembur(
                                                    paginationLembur.prev_page_url
                                                )
                                            "
                                            class="btn-modern btn-primary p-1"
                                            style="
                                                border-radius: 8px !important;
                                            "
                                        >
                                            <i
                                                class="bi bi-arrow-left d-flex justify-content-center align-items-center"
                                            ></i>
                                        </button>
                                        <span
                                            >{{ paginationLembur.current_page }}
                                            of
                                            {{
                                                paginationLembur.last_page
                                            }}</span
                                        >
                                        <button
                                            :disabled="
                                                paginationLembur.current_page ==
                                                paginationLembur.last_page
                                            "
                                            @click="
                                                getAllLembur(
                                                    paginationLembur.next_page_url
                                                )
                                            "
                                            class="btn-modern btn-primary p-1"
                                            style="
                                                border-radius: 8px !important;
                                            "
                                        >
                                            <i
                                                class="bi bi-arrow-right d-flex justify-content-center align-items-center"
                                            ></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" style="height: 19.5rem">
                                    <div
                                        v-for="(data, idx) in allDataLembur"
                                        :key="idx"
                                        class="row fs-6 mt-3 px-3"
                                    >
                                        <div
                                            style="font-size: 0.9rem !important"
                                            class="col-4 p-0 d-flex justify-content-start align-items-center"
                                        >
                                            {{
                                                formatTanggal_Lembur(
                                                    data.Tanggal_Lembur_Dari,
                                                    data.Tanggal_Lembur_Sampai
                                                )
                                            }}
                                        </div>
                                        <div
                                            v-tooltip="
                                                data.Tanggal_Lembur_Dari +
                                                '-' +
                                                data.Tanggal_Lembur_Sampai
                                            "
                                            style="
                                                font-size: 0.9rem !important;
                                                cursor: pointer;
                                            "
                                            class="col-4 p-0 d-flex justify-content-center align-items-center"
                                        >
                                            {{
                                                truncateText(
                                                    data.durasi?.toFixed(2),
                                                    4
                                                )
                                            }}
                                            jam
                                        </div>
                                        <div
                                            style="font-size: 0.7rem !important"
                                            class="col-4 p-0 d-flex justify-content-center align-items-center"
                                        >
                                            <span
                                                class=""
                                                :class="
                                                    data.Status == 'Disetujui'
                                                        ? 'badge-lembur-selesai'
                                                        : 'badge-lembur'
                                                "
                                            >
                                                {{ data.Status }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- <button
                                class="btn btn-primary w-100 mt-3"
                                @click="newLeaveRequest"
                            >
                                <i class="bi bi-plus-circle"></i> Buat Pengajuan
                                Baru
                            </button> -->
                                </div>
                            </div>
                        </div>
                        <div v-else class="skeleton-container">
                            <div class="card leave-requests-card">
                                <!-- Card Header Skeleton -->
                                <div
                                    class="card-header d-flex justify-content-between align-items-center skeleton-container"
                                >
                                    <div
                                        class="skeleton-text"
                                        style="width: 120px; height: 24px"
                                    ></div>
                                    <div
                                        class="d-flex gap-2 align-items-center"
                                    >
                                        <div
                                            class="skeleton-button"
                                            style="
                                                width: 32px;
                                                height: 32px;
                                                border-radius: 8px;
                                            "
                                        ></div>
                                        <div
                                            class="skeleton-text"
                                            style="width: 60px; height: 20px"
                                        ></div>
                                        <div
                                            class="skeleton-button"
                                            style="
                                                width: 32px;
                                                height: 32px;
                                                border-radius: 8px;
                                            "
                                        ></div>
                                    </div>
                                </div>

                                <!-- Card Body Skeleton -->
                                <div class="card-body" style="height: 19.5rem">
                                    <!-- Repeat skeleton rows (typically 3-5 items) -->
                                    <div
                                        class="row fs-6 mt-3 skeleton-row"
                                        v-for="i in 4"
                                        :key="i"
                                    >
                                        <div
                                            class="col-4 d-flex justify-content-center align-items-center"
                                        >
                                            <div
                                                class="skeleton-text"
                                                style="width: 80%; height: 16px"
                                            ></div>
                                        </div>
                                        <div
                                            class="col-4 d-flex justify-content-center align-items-center"
                                        >
                                            <div
                                                class="skeleton-text"
                                                style="width: 90%; height: 16px"
                                            ></div>
                                        </div>
                                        <div
                                            class="col-4 d-flex justify-content-center align-items-center"
                                        >
                                            <div
                                                class="skeleton-badge"
                                                style="
                                                    width: 70px;
                                                    height: 24px;
                                                    border-radius: 12px;
                                                "
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="shift-timeline">
                            <div class="card shadow-sm border-0">
                                <div
                                    class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center"
                                >
                                    <h5 class="mb-0 text-white">
                                        <i
                                            class="bi bi-calendar-week me-2 text-white"
                                        ></i
                                        >{{ currentMonthAndDate }}
                                    </h5>
                                    <select
                                        @change="getChartData"
                                        v-model="chartStateLoad"
                                        class="btn-modern btn-primary p-1"
                                        style="border-radius: 8px !important"
                                    >
                                        <option value="week">Perminggu</option>
                                        <option value="month">Perbulan</option>
                                        <option value="year">Pertahun</option>
                                    </select>
                                </div>
                                <div
                                    v-if="!loadingChart"
                                    style="height: 19rem"
                                    class="p-3 d-md-flex flex-column justify-content-center gap-2"
                                >
                                    <div id="chart-donut">
                                        <apexchart
                                            type="donut"
                                            width="250"
                                            :options="chartOptions"
                                            :series="series"
                                        >
                                        </apexchart>
                                    </div>
                                    <div class="legend-manual row">
                                        <div
                                            class="legend-item col-6"
                                            v-for="(
                                                label, index
                                            ) in chartOptions.labels"
                                            :key="index"
                                        >
                                            <span
                                                class="legend-dot"
                                                :style="{
                                                    backgroundColor:
                                                        chartOptions.colors[
                                                            index
                                                        ],
                                                }"
                                            ></span>
                                            <span class="legend-text"
                                                >{{ label }}
                                                {{ series[index] }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                                <!-- Skeleton -->
                                <!-- Card Body Skeleton -->
                                <div
                                    v-else
                                    style="height: 19rem"
                                    class="p-7 d-md-flex flex-column justify-content-center gap-2"
                                >
                                    <!-- Donut Chart Placeholder -->
                                    <div
                                        id="chart-donut"
                                        class="d-flex justify-content-center"
                                    >
                                        <div
                                            class="skeleton-chart my-2"
                                            style="
                                                width: 225px;
                                                height: 225px;
                                                border-radius: 50%;
                                            "
                                        ></div>
                                    </div>

                                    <!-- Legend Placeholder -->
                                    <div class="legend-manual row">
                                        <div
                                            class="legend-item col-6 mb-2"
                                            v-for="i in 4"
                                            :key="i"
                                        >
                                            <div
                                                class="d-flex align-items-center"
                                            >
                                                <div
                                                    class="skeleton-legend-dot"
                                                    style="
                                                        width: 12px;
                                                        height: 12px;
                                                        border-radius: 50%;
                                                        margin-right: 8px;
                                                    "
                                                ></div>
                                                <div
                                                    class="skeleton-legend-text"
                                                    style="
                                                        width: 80px;
                                                        height: 16px;
                                                    "
                                                ></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="!loadingShift" class="p3">
                    <div class="shift-timeline">
                        <div class="card shadow-sm border-0">
                            <div
                                class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center"
                            >
                                <h5 class="mb-0 text-white">
                                    <i
                                        class="bi bi-calendar-week me-2 text-white"
                                    ></i
                                    >Jadwal Shift
                                </h5>
                                <div class="date-range fw-bold">
                                    {{ getJustYear(dataShift[0]?.Tanggal) }}
                                </div>
                            </div>

                            <div class="card-body pt-4 pb-3">
                                <!-- 7-Day Timeline -->
                                <div class="timeline-container">
                                    <div class="timeline-days">
                                        <div
                                            v-for="day in dataShift"
                                            :key="day.Tanggal"
                                            class="timeline-day"
                                            :class="{
                                                active: day.isToday,
                                                'day-off':
                                                    day.shiftType ===
                                                    'NO SHIFT',
                                            }"
                                            @click="selectDay(day)"
                                        >
                                            <div class="day-header">
                                                <div class="day-name">
                                                    {{ day.Nama_Shift }}
                                                </div>
                                                <div class="day-date">
                                                    {{
                                                        formatTanggaldanBulan(
                                                            day.Tanggal
                                                        )
                                                    }}
                                                </div>
                                            </div>
                                            <div class="day-shift text-white">
                                                <span
                                                    class="shift-badge"
                                                    :class="
                                                        shiftBadgeClass(day)
                                                    "
                                                >
                                                    {{ day.Nama_Shift }}
                                                </span>
                                                <div
                                                    class="shift-hours"
                                                    v-if="
                                                        day.Nama_Shift !==
                                                        'NO SHIFT'
                                                    "
                                                >
                                                    {{ day.Jam_Masuk }} -
                                                    {{ day.Jam_Keluar }}
                                                </div>
                                                <div
                                                    v-else
                                                    class="day-off-text"
                                                >
                                                    <i class="bi bi-moon"></i>
                                                    OFF
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="d-flex gap-3 justify-content-center align-items-center mt-3"
                                >
                                    <button
                                        @click="prevWeekShift"
                                        class="btn-modern btn-primary rounded-3 px-3 py-2"
                                    >
                                        <i
                                            class="bi bi-chevron-left d-flex justify-content-center align-items-center"
                                        ></i>
                                    </button>
                                    <div class="">
                                        {{
                                            getJustMonth(dataShift[0]?.Tanggal)
                                        }}
                                    </div>
                                    <button
                                        @click="nextWeekShift"
                                        class="btn-modern btn-primary rounded-3 px-3 py-2"
                                    >
                                        <i
                                            class="bi bi-chevron-right d-flex justify-content-center align-items-center"
                                        ></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="skeleton-container">
                    <div class="card shadow-sm border-0">
                        <!-- Card Header Skeleton -->
                        <div
                            class="card-header bg-gradient-primary text-white d-flex justify-content-between align-items-center"
                        >
                            <div class="d-flex align-items-center">
                                <div
                                    class="skeleton-icon"
                                    style="
                                        width: 24px;
                                        height: 24px;
                                        margin-right: 8px;
                                        border-radius: 4px;
                                    "
                                ></div>
                                <div
                                    class="skeleton-text"
                                    style="width: 120px; height: 24px"
                                ></div>
                            </div>
                            <div
                                class="skeleton-text"
                                style="width: 60px; height: 24px"
                            ></div>
                        </div>

                        <!-- Card Body Skeleton -->
                        <div class="card-body pt-4 pb-3">
                            <!-- 7-Day Timeline Skeleton -->
                            <div class="timeline-container">
                                <div class="timeline-days">
                                    <div
                                        class="timeline-day skeleton-day"
                                        v-for="i in 7"
                                        :key="i"
                                    >
                                        <div class="day-header">
                                            <div
                                                class="skeleton-text"
                                                style="
                                                    width: 80%;
                                                    height: 16px;
                                                    margin-bottom: 8px;
                                                "
                                            ></div>
                                            <div
                                                class="skeleton-text"
                                                style="width: 60%; height: 14px"
                                            ></div>
                                        </div>
                                        <div class="day-shift">
                                            <div
                                                class="skeleton-badge"
                                                style="
                                                    width: 80%;
                                                    height: 24px;
                                                    border-radius: 12px;
                                                    margin-bottom: 4px;
                                                "
                                            ></div>
                                            <div
                                                class="skeleton-text"
                                                style="width: 90%; height: 14px"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Controls Skeleton -->
                            <div
                                class="d-flex gap-3 justify-content-center align-items-center mt-3"
                            >
                                <div
                                    class="skeleton-button"
                                    style="
                                        width: 40px;
                                        height: 40px;
                                        border-radius: 8px;
                                    "
                                ></div>
                                <div
                                    class="skeleton-text"
                                    style="width: 100px; height: 20px"
                                ></div>
                                <div
                                    class="skeleton-button"
                                    style="
                                        width: 40px;
                                        height: 40px;
                                        border-radius: 8px;
                                    "
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-between">
                    <div v-if="!loadingIzin" class="col-md-6">
                        <div class="card leave-requests-card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <h5 class="mb-0">Pengajuan Izin</h5>
                                <div class="d-flex gap-2">
                                    <button
                                        :disabled="
                                            paginationIzin.current_page <= 1
                                        "
                                        @click="
                                            getAllIzin(
                                                paginationIzin.prev_page_url
                                            )
                                        "
                                        class="btn-modern btn-primary p-1"
                                        style="border-radius: 8px !important"
                                    >
                                        <i
                                            class="bi bi-arrow-left d-flex justify-content-center align-items-center"
                                        ></i>
                                    </button>
                                    <span
                                        >{{ paginationIzin.current_page }}
                                        of
                                        {{ paginationIzin.last_page }}</span
                                    >
                                    <button
                                        :disabled="
                                            paginationIzin.current_page ==
                                            paginationIzin.last_page
                                        "
                                        @click="
                                            getAllIzin(
                                                paginationIzin.next_page_url
                                            )
                                        "
                                        class="btn-modern btn-primary p-1"
                                        style="border-radius: 8px !important"
                                    >
                                        <i
                                            class="bi bi-arrow-right d-flex justify-content-center align-items-center"
                                        ></i>
                                    </button>
                                </div>
                            </div>
                            <div
                                class="card-body d-flex flex-column justify-content-between"
                                style="height: 13rem"
                            >
                                <div>
                                    <div
                                        class="leave-request py-3"
                                        v-for="(request, idx) in allDataIzin"
                                        :key="idx"
                                    >
                                        <div
                                            class="request-type"
                                            :class="
                                                getTypeClass(request.Tipe_Izin)
                                            "
                                        >
                                            {{ parseIzin(request.Tipe_Izin) }}
                                        </div>
                                        <div class="request-dates">
                                            {{
                                                formatTanggal_Izin(
                                                    request.Tanggal_Mulai,
                                                    request.Tanggal_Selesai
                                                )
                                            }}
                                        </div>
                                        <div
                                            class="request-status"
                                            :class="'status-' + request.status"
                                        >
                                            {{ request.status }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="col-md-6 skeleton-container">
                        <div class="card leave-requests-card">
                            <!-- Card Header Skeleton -->
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <div
                                    class="skeleton-text"
                                    style="width: 120px; height: 24px"
                                ></div>
                                <div class="d-flex gap-2 align-items-center">
                                    <div
                                        class="skeleton-button"
                                        style="
                                            width: 32px;
                                            height: 32px;
                                            border-radius: 8px;
                                        "
                                    ></div>
                                    <div
                                        class="skeleton-text"
                                        style="width: 60px; height: 20px"
                                    ></div>
                                    <div
                                        class="skeleton-button"
                                        style="
                                            width: 32px;
                                            height: 32px;
                                            border-radius: 8px;
                                        "
                                    ></div>
                                </div>
                            </div>

                            <!-- Card Body Skeleton -->
                            <div
                                class="card-body d-flex flex-column justify-content-between"
                                style="height: 19.1rem"
                            >
                                <!-- Leave Request Items (4-5 items typical) -->
                                <div class="">
                                    <div
                                        class="leave-request-skeleton py-1"
                                        v-for="i in 5"
                                        :key="i"
                                    >
                                        <div
                                            class="d-flex justify-content-between align-items-center mb-3"
                                        >
                                            <div
                                                class="skeleton-badge"
                                                style="
                                                    width: 80px;
                                                    height: 24px;
                                                    border-radius: 12px;
                                                "
                                            ></div>
                                            <div
                                                class="skeleton-text"
                                                style="
                                                    width: 120px;
                                                    height: 16px;
                                                "
                                            ></div>
                                            <div
                                                class="skeleton-badge"
                                                style="
                                                    width: 70px;
                                                    height: 24px;
                                                    border-radius: 12px;
                                                "
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- "Lihat Selengkapnya" Button Skeleton -->
                                <div
                                    class="skeleton-button"
                                    style="
                                        width: 100%;
                                        height: 40px;
                                        border-radius: 8px;
                                        margin-top: 16px;
                                    "
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- LEMBUR -->

                        <div class="card leave-requests-card">
                            <div
                                class="card-header d-flex justify-content-between align-items-center"
                            >
                                <h5 class="mb-0">Tergabung pada team</h5>
                                <div class="d-flex gap-2">
                                    <button
                                        @click="prevPageTergabung"
                                        class="btn-modern btn-primary p-1"
                                        style="border-radius: 8px !important"
                                    >
                                        <i
                                            class="bi bi-arrow-left d-flex justify-content-center align-items-center"
                                        ></i>
                                    </button>
                                    <span
                                        >{{ currentPageTergabung }}
                                        of
                                        {{ totalPagesTergabung }}</span
                                    >
                                    <button
                                        @click="nextPageTergabung"
                                        class="btn-modern btn-primary p-1"
                                        style="border-radius: 8px !important"
                                    >
                                        <i
                                            class="bi bi-arrow-right d-flex justify-content-center align-items-center"
                                        ></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body" style="height: 13rem">
                                <div
                                    v-for="(data, idx) in paginatedTergabung"
                                    :key="idx"
                                    class="row fs-6 mt-3 ps-3"
                                >
                                    <div
                                        v-tooltip="data.Nama"
                                        style="font-size: 0.9rem !important"
                                        class="col-4 p-0 d-flex justify-content-start text-start align-items-center"
                                    >
                                        {{
                                            truncateText(
                                                capitalize(data?.Nama),
                                                10
                                            )
                                        }}
                                    </div>
                                    <div
                                        v-tooltip="data.divisi"
                                        style="
                                            font-size: 0.9rem !important;
                                            cursor: pointer;
                                        "
                                        class="col-4 p-0 d-flex justify-content-center align-items-center"
                                    >
                                        {{ truncateText(data.divisi, 10) }}
                                    </div>
                                    <div
                                        style="font-size: 0.7rem !important"
                                        class="col-4 p-0 d-flex justify-content-center align-items-center"
                                    >
                                        <span
                                            class=""
                                            :class="
                                                data.Status == 'Disetujui'
                                                    ? 'badge-lembur-selesai'
                                                    : 'badge-lembur'
                                            "
                                        >
                                            {{ data.HP }}
                                        </span>
                                    </div>
                                </div>

                                <!-- <button
                                class="btn btn-primary w-100 mt-3"
                                @click="newLeaveRequest"
                            >
                                <i class="bi bi-plus-circle"></i> Buat Pengajuan
                                Baru
                            </button> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- buletin  -->

        <div
            class="modal-backdrop"
            v-if="showModalBuletin"
            @click.self="showModalBuletin = false"
        >
            <div class="glass-modal">
                <div class="glass-modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-white">
                            {{ judulBerita }}
                        </h5>
                        <button
                            type="button"
                            class="close"
                            @click="showModalBuletin = false"
                        >
                            &times;
                        </button>
                    </div>
                    <div v-html="convertedHtml" class="modal-body"></div>
                    <div class="modal-footer">
                        <!-- <button
                            type="button"
                            class="btn-modern btn-secondary me-2"
                            @click="showModalBuletin = false"
                        >
                            Tutup
                        </button> -->
                        <button
                            @click="showModalBuletin = false"
                            type="button"
                            class="btn-modern btn-primary"
                        >
                            Mengerti
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Shift Information -->
        <div class="row"></div>
    </div>
</template>

<script>
import axios from "axios";
import VueApexCharts from "vue3-apexcharts";
import { marked } from "marked"; // Untuk konversi Markdown ke HTML
import {
    ElDialog,
    ElCalendar,
    ElMessage,
    ElTag,
    ElTooltip,
    ElSteps,
    ElStep,
    ElNotification,
    ElSlider,
} from "element-plus";
import "element-plus/dist/index.css";
import DOMPurify from "dompurify"; // Untuk sanitasi HTML (SANGAT PENTING!)
import { capitalize } from "vue";

export default {
    components: {
        apexchart: VueApexCharts,
        ElDialog,
        ElCalendar,
        ElMessage,
        ElSteps,
        ElStep,
        ElNotification,
        ElTooltip,
        ElSlider,
        ElTag,
    },
    props: {
        accessPage: Array,
        namaKaryawan: String,
        shiftHariIni: String,
        Jam_Masuk: String,
        Jam_Keluar: String,
        shiftBesok: String,
        totalTerlambat: String,
        totalLembur: String,
        sisaCuti: String,
        judulBerita: String,
        isiBerita: String,
        nama_mengapprove: Array,
        nama_Approver: Array,
        nama_tergabung: Array,
        nama_anggota: Array,
    },
    watch: {
        // Ini penting jika prop bisa berubah setelah komponen di-mount
    },
    name: "HRISDashboard",
    data() {
        return {
            itemPerPageCard: 5,

            serachQueryTergabung: "",
            currentPageTergabung: 1,

            serachQueryAnggota: "",
            currentPageAnggota: 1,

            convertedHtml: "",
            showModalBuletin: false,

            loadingShift: false,
            loadingChart: false,
            loadingTop: false,
            loadingCard: false,
            loadingLembur: false,
            loadingIzin: false,
            loadingQuick: false,
            loadingCalendar: false,

            paginationLembur: [],
            paginationIzin: [],
            allDataIzin: [],
            allDataLembur: [],
            chartStateLoad: "month",
            dataShift: [],
            dataDateAbsen: [],
            weekSchedule: [
                {
                    dayName: "Sun",
                    date: "Jul 27",
                    shiftType: "Non Shift",
                    startTime: "08:00",
                    endTime: "16:30",
                    shiftCode: "HARI INI",
                    isToday: true,
                },
                {
                    dayName: "Mon",
                    date: "Jul 28",
                    shiftType: "OGK2",
                    startTime: "08:00",
                    endTime: "17:00",
                    shiftCode: "OGK2",
                    isToday: false,
                },
                {
                    dayName: "Tue",
                    date: "Jul 29",
                    shiftType: "Regular",
                    startTime: "08:00",
                    endTime: "17:00",
                    isToday: false,
                },
                {
                    dayName: "Wed",
                    date: "Jul 30",
                    shiftType: "Regular",
                    startTime: "08:00",
                    endTime: "17:00",
                    isToday: false,
                },
                {
                    dayName: "Thu",
                    date: "Jul 31",
                    shiftType: "Regular",
                    startTime: "08:00",
                    endTime: "17:00",
                    isToday: false,
                },
                {
                    dayName: "Fri",
                    date: "Aug 1",
                    shiftType: "Regular",
                    startTime: "08:00",
                    endTime: "17:00",
                    isToday: false,
                },
                {
                    dayName: "Sat",
                    date: "Aug 2",
                    shiftType: "Day Off",
                    isToday: false,
                },
            ],
            selectedDay: {},

            series: [20, 5, 2, 3], // Sesuai dengan "Kehadiran", "Terlambat", "Pulang cepat", "Izin"

            // Konfigurasi untuk tampilan chart
            chartOptions: {
                chart: {
                    type: "donut",
                },
                // Label untuk setiap segmen donut
                labels: ["Kehadiran", "Izin Terlambat", "Pulang cepat", "Izin"],
                // Warna untuk setiap segmen
                colors: ["#00BFFF", "#FFD700", "#FF0000", "#32CD32"], // Contoh warna yang mirip
                // Konfigurasi untuk tampilan di tengah donut
                plotOptions: {
                    pie: {
                        donut: {
                            size: "65%", // Ukuran lubang tengah
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: "Persentase kehadiran",
                                    formatter: () => "86%", // Teks persentase di tengah
                                    fontSize: "16px",
                                    fontWeight: 600,
                                },
                                name: {
                                    show: false, // Menyembunyikan nama di bawah persentase
                                },
                                value: {
                                    show: false, // Menyembunyikan nilai angka di bawah persentase
                                },
                            },
                        },
                    },
                },
                // Konfigurasi untuk legend (keterangan)
                legend: {
                    show: false, // Menyembunyikan legend bawaan, karena kita akan buat manual
                },
                // Mengubah warna teks di tengah
                dataLabels: {
                    enabled: false,
                },
            },
            // TANGGAL
            currentDate: new Date(),
            DayName: ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"],
            //AKHIR TANGGAL
            userName: "Ridho",
            currentDate: "",
            currentTime: "",
            currentMonthAndDate: "",
            stats: {
                overtime: 8.5,
                leaveBalance: 12,
                sickLeave: 5,
                pendingApproval: 3,
            },
            attendanceSummary: {
                present: 18,
                late: 2,
                earlyLeave: 1,
                absent: 0,
                workingDays: 21,
            },
            quickActions: [
                {
                    id: "attendance",
                    label: "Absensi",
                    icon: "bi bi-person-check",
                    class: "btn-attendance",
                    akses: "absensiPage",
                    url: "/absensiSales",
                },
                {
                    id: "overtimeAdmin",
                    label: "Lembur Admin",
                    icon: "bi bi-person-video2",
                    class: "btn-overtime",
                    akses: "OvertimeManagementAdmin",
                    url: "/overtimeAdmin",
                },
                {
                    id: "overtime",
                    label: "Lembur",
                    icon: "bi bi-clock-history",
                    class: "btn-overtime",
                    akses: "OvertimeManagement",
                    url: "/overtime",
                },

                {
                    id: "IzinPageAdmin",
                    label: "Izin Admin",
                    icon: "bi bi-back",
                    class: "btn-leave",
                    akses: "IzinPageAdmin",
                    url: "/izinAdmin",
                },
                {
                    id: "IzinPageApprover",
                    label: "Izin Approval",
                    icon: "bi bi-ui-checks-grid",
                    class: "btn-leave",
                    akses: "IzinPageApprover",
                    url: "/izinLevelUp",
                },
                // {
                //     id: "leave",
                //     label: "Cuti",
                //     icon: "bi bi-airplane",
                //     class: "btn-leave",
                //     akses: "izinPage",
                //     url: "/izin",
                // },
                {
                    id: "sick",
                    label: "Sakit",
                    icon: "bi bi-heart-pulse",
                    class: "btn-sick",
                    akses: "izinPage",
                    url: "/izin",
                },
                {
                    id: "permission",
                    label: "Izin",
                    icon: "bi bi-chat-square-text",
                    class: "btn-permission",
                    akses: "izinPage",
                    url: "/izin",
                },
                {
                    id: "shiftAdmin",
                    label: "Shift Admin",
                    icon: "bi bi-arrow-left-right",
                    class: "btn-shift",
                    akses: "ShiftManagementAdmin",
                    url: "/swapShiftAdmin",
                },
                {
                    id: "addKaryawanTeam",
                    label: "Setting Users",
                    icon: "bi bi-gear",
                    class: "btn-shift",
                    akses: "addKaryawanTeam",
                    url: "/add-karyawan-team",
                },
                {
                    id: "shift",
                    label: "Shift",
                    icon: "bi bi-arrow-left-right",
                    class: "btn-shift",
                    akses: "ShiftManagement",
                    url: "/swapShift",
                },
            ],
            upcomingHoliday: {
                id: 1,
                name: "Independence Day",
                date: "August 17, 2023",
                daysLeft: 12,
            },
            nextHolidays: [
                { id: 2, name: "Christmas Day", date: "Dec 25, 2023" },
                { id: 3, name: "New Year", date: "Jan 1, 2024" },
            ],
            recentLeaveRequests: [
                {
                    id: 1,
                    type: "annual",
                    typeLabel: "Pulang Cepat",
                    startDate: "Jul 10",
                    endDate: "Jul 12",
                    status: "Approved",
                },
                {
                    id: 2,
                    type: "sick",
                    typeLabel: "Sakit",
                    startDate: "Jun 28",
                    endDate: "Jun 29",
                    status: "Approved",
                },
                {
                    id: 3,
                    type: "permission",
                    typeLabel: "Izin",
                    startDate: "Jun 15",
                    endDate: "Jun 15",
                    status: "Pending",
                },
                {
                    id: 3,
                    type: "permission",
                    typeLabel: "Izin",
                    startDate: "Jun 15",
                    endDate: "Jun 15",
                    status: "Pending",
                },
                {
                    id: 3,
                    type: "permission",
                    typeLabel: "Izin",
                    startDate: "Jun 15",
                    endDate: "Jun 15",
                    status: "Pending",
                },
            ],
            currentShift: {
                id: 1,
                name: "Regular Shift",
                startTime: "08:00",
                endTime: "17:00",
            },
            upcomingShifts: [
                {
                    id: 2,
                    day: "Tomorrow",
                    name: "Regular Shift",
                    startTime: "08:00",
                    endTime: "17:00",
                },
                {
                    id: 3,
                    day: "Friday",
                    name: "Regular Shift",
                    startTime: "08:00",
                    endTime: "17:00",
                },
                {
                    id: 4,
                    day: "Saturday",
                    name: "Day Off",
                    startTime: "-",
                    endTime: "-",
                },
            ],
            shiftProgress: 45,
            shiftTimeLeft: "4 hours 30 minutes",
        };
    },
    created() {
        this.selectedDay =
            this.weekSchedule.find((day) => day.isToday) ||
            this.weekSchedule[0];
    },
    computed: {
        filteredAnggota() {
            const filtered = this.nama_anggota;

            // console.log(this.nama_anggota);

            if (this.serachQueryAnggota) {
                this.currentPageAnggota = 1;
                filtered = filtered.filter((emp) => {
                    emp.Nama.toLowerCase().includes(
                        this.serachQueryAnggota.toLowerCase()
                    );
                });
            }

            return filtered;
        },
        paginatedAnggota() {
            const start = (this.currentPageAnggota - 1) * this.itemPerPageCard;
            const end = start + this.itemPerPageCard;
            return this.filteredAnggota.slice(start, end);
        },
        totalPagesAnggota() {
            return Math.ceil(
                this.filteredAnggota.length / this.itemPerPageCard
            );
        },
        filteredTergabung() {
            const filtered = this.nama_tergabung;

            // console.log(this.nama_anggota);

            if (this.serachQueryTergabung) {
                this.currentPageTergabung = 1;
                filtered = filtered.filter((emp) => {
                    emp.Nama.toLowerCase().includes(
                        this.serachQueryTergabung.toLowerCase()
                    );
                });
            }

            return filtered;
        },
        paginatedTergabung() {
            const start =
                (this.currentPageTergabung - 1) * this.itemPerPageCard;
            const end = start + this.itemPerPageCard;
            return this.filteredTergabung.slice(start, end);
        },
        totalPagesTergabung() {
            return Math.ceil(
                this.filteredTergabung.length / this.itemPerPageCard
            );
        },
        filteredQuickActions() {
            // Normalize access page names to match case (optional)
            const normalizedAccessPages = this.accessPage.map((page) =>
                page.Jenis_page.toLowerCase().replace(/ /g, "")
            );

            return this.quickActions.filter((action) => {
                // Normalize akses field for comparison
                const normalizedAkses = action.akses
                    .toLowerCase()
                    .replace(/ /g, "");
                return normalizedAccessPages.includes(normalizedAkses);
            });
        },

        attendancePercentage() {
            const total =
                this.attendanceSummary.present +
                this.attendanceSummary.late +
                this.attendanceSummary.earlyLeave +
                this.attendanceSummary.absent;
            return total > 0
                ? Math.round((this.attendanceSummary.present / total) * 100)
                : 0;
        },
        currentMonth() {
            return this.currentDate.getMonth();
        },
        currentYear() {
            return this.currentDate.getFullYear();
        },
        monthName() {
            return this.currentDate.toLocaleDateString("id-ID", {
                month: "long",
            });
        },
        calendarDays() {
            const year = this.currentYear;
            const month = this.currentMonth;

            const firstDay = new Date(year, month, 1).getDay();
            const lastDay = new Date(year, month + 1, 0).getDate();

            const days = [];

            // Tambahkan cell kosong untuk hari sebelum tanggal 1
            for (let i = 0; i < firstDay; i++) {
                days.push({ date: null });
            }

            for (let date = 1; date <= lastDay; date++) {
                const fullDate = `${year}-${(month + 1)
                    .toString()
                    .padStart(2, "0")}-${date.toString().padStart(2, "0")}`;
                const today = new Date();
                const isToday =
                    today.getFullYear() === year &&
                    today.getMonth() === month &&
                    today.getDate() === date;
                const customClass = this.customDayClasses[fullDate] || "";

                days.push({ date, isToday, customClass });
            }
            return days;
        },
    },
    methods: {
        nextPageTergabung() {
            if (this.currentPageAnggota < this.totalPagesAnggota)
                this.currentPageAnggota++;
        },
        prevPageTergabung() {
            if (this.currentPageAnggota > 1) this.currentPageAnggota--;
        },
        nextPageAnggota() {
            if (this.currentPageAnggota < this.totalPagesAnggota)
                this.currentPageAnggota++;
        },
        prevPageAnggota() {
            if (this.currentPageAnggota > 1) this.currentPageAnggota--;
        },
        convertAndSanitize(markdown) {
            if (markdown) {
                const rawHtml = marked(markdown);
                this.convertedHtml = DOMPurify.sanitize(rawHtml);
            } else {
                this.convertedHtml = ""; // Kosongkan jika tidak ada markdown
            }
        },
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
        async getAllIzin(url) {
            try {
                this.loadingIzin = true;
                const response = await axios
                    .get(url)
                    .then((response) => {
                        this.allDataIzin = response.data.data;
                        this.paginationIzin = response.data;
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            } catch (error) {
                console.log(error);
            } finally {
                this.loadingIzin = false;
            }
        },
        async getChartData() {
            try {
                this.loadingChart = true;
                const response = await axios.get("/uDash/getChartData", {
                    params: { jenis: this.chartStateLoad },
                });
                if (response.status == 200) {
                    this.series = response.data.data;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loadingChart = false;
            }
        },
        formatTanggaldanBulan(tanggalString) {
            if (!tanggalString) {
                return "";
            }

            const namaBulan = [
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

            const tanggalObjek = new Date(tanggalString);
            const tanggal = tanggalObjek.getDate();
            const bulan = namaBulan[tanggalObjek.getMonth()];

            return `${tanggal} ${bulan}`;
        },
        async getDataShift() {
            try {
                this.loadingShift = true;
                const response = await axios.get("/uDash/getDataShift");
                if (response.status == 200) {
                    this.dataShift = response.data.data;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loadingShift = false;
            }
        },
        async prevWeekShift() {
            try {
                this.loadingShift = true;
                const response = await axios.get("/uDash/getDataShift", {
                    params: {
                        tanggal: this.dataShift[0].Tanggal,
                        jenis: "prev",
                    },
                });
                if (response.status == 200) {
                    this.dataShift = response.data.data;
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loadingShift = false;
            }
        },
        async nextWeekShift() {
            try {
                this.loadingShift = true;

                const response = await axios.get("/uDash/getDataShift", {
                    params: {
                        tanggal: this.dataShift[0].Tanggal,
                        jenis: "next",
                    },
                });
                if (response.status == 200) {
                    this.dataShift = response.data.data;
                    // console.log(this.dataShift);
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loadingShift = false;
            }
        },
        async prevWeekAbsensi() {
            try {
                this.loadingCalendar = true;
                const response = await axios.get("/uDash/getDateAbsen", {
                    params: {
                        tanggal: this.dataDateAbsen[0].Tanggal,
                        jenis: "prev",
                    },
                });
                if (response.status == 200) {
                    this.dataDateAbsen = response.data.data;
                    // console.log(this.dataDateAbsen);
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loadingCalendar = false;
            }
        },
        async nextWeekAbsensi() {
            try {
                this.loadingCalendar = true;

                const response = await axios.get("/uDash/getDateAbsen", {
                    params: {
                        tanggal: this.dataDateAbsen[0].Tanggal,
                        jenis: "next",
                    },
                });
                if (response.status == 200) {
                    this.dataDateAbsen = response.data.data;
                    // console.log(this.dataDateAbsen);
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loadingCalendar = false;
            }
        },
        getJustDate(dateString) {
            // Pastikan input adalah string yang valid
            if (!dateString || typeof dateString !== "string") {
                return null;
            }
            let part1 = dateString.split(" ")[0];
            // Pisahkan string "2025-08-04" menjadi array ["2025", "08", "04"]
            const dateParts = part1.split("-");

            // Ambil elemen terakhir dari array (yaitu "04")
            return dateParts[dateParts.length - 1];
        },
        getJustMonthAndYear(dateString) {
            if (!dateString || typeof dateString !== "string") {
                return null;
            }
            const months = [
                "Januari",
                "Februari",
                "Maret",
                "April",
                "Mei",
                "Juni",
                "Juli",
                "Agustus",
                "September",
                "Oktober",
                "November",
                "Desember",
            ];
            const date = new Date(dateString);
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            return `${month} ${year}`;
        },
        getJustMonth(dateString) {
            if (!dateString || typeof dateString !== "string") {
                return null;
            }
            const months = [
                "Januari",
                "Februari",
                "Maret",
                "April",
                "Mei",
                "Juni",
                "Juli",
                "Agustus",
                "September",
                "Oktober",
                "November",
                "Desember",
            ];
            const date = new Date(dateString);
            const month = months[date.getMonth()];
            return `${month}`;
        },
        getJustYear(dateString) {
            if (!dateString || typeof dateString !== "string") {
                return null;
            }
            const date = new Date(dateString);
            const year = date.getFullYear();

            return `${year}`;
        },

        // Fungsi untuk mendapatkan nama hari singkat (contoh: "Sen")
        getJustDay(dateString) {
            if (!dateString || typeof dateString !== "string") {
                return null;
            }
            const days = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
            const date = new Date(dateString);
            return days[date.getDay()];
        },
        getClassAbsensi(data) {
            if (!data) return "";

            if (data.Nama_Shift != "NO SHIFT" && !data.CheckIn) {
                return "dateCard weekday";
            }
            // Prioritize specific conditions
            if (data.Cuti_No_Transaksi || data.CutiHutang_No_Transaksi) {
                return "dateCard holiday"; // Leave
            }
            if (data.Sakit_No_Transaksi || data.Izin_No_Transaksi) {
                return "dateCard holiday"; // Sick/Permission
            }

            // Check for non-shift days
            const date = new Date(data.Tanggal);
            const dayOfWeek = date.getDay(); // 0 = Sunday, 1 = Monday, etc.

            if (data.Nama_Shift === "NO SHIFT") {
                // If it's a Sunday and has 'NO SHIFT', classify as 'hari-libur'
                if (dayOfWeek === 0) {
                    return "dateCard lost"; // Sunday holiday
                }
                // Otherwise, it's a general non-shift day
                return "dateCard lost";
            }

            // Default case for a regular shift
            return "dateCard";
        },
        getTooltipContent(itemData) {
            // Check if itemData exists
            if (!itemData) {
                return `
        <div class="dashboard-tooltip no-data">
            <div class="tooltip-content">
                <span class="status-icon">ℹ️</span>
                <span>No shift data available</span>
            </div>
        </div>
         <style>
        .dashboard-tooltip {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            max-width: 240px;
            padding: 12px;
            border-radius: 10px;
            background: #FFFFFF;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            font-size: 13px;
            color: #2D3748;
            border: 1px solid #E2E8F0;
        }

        /* Status Card Styling */
        .status-card {
            text-align: center;
            padding: 12px 16px;
        }

        .status-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .status-icon {
            font-size: 18px;
        }

        .status-title {
            font-weight: 600;
            font-size: 14px;
            color: #2D3748;
        }

        .status-dates {
            font-size: 12px;
            color: #718096;
            margin-bottom: 6px;
        }

        .status-footer {
            font-size: 11px;
            color: #718096;
            padding-top: 4px;
            border-top: 1px dashed #E2E8F0;
        }

        /* Status-specific colors */
        .cuti {
            background-color: #EBF8FF;
            border-color: #BEE3F8;
        }

        .sakit {
            background-color: #FFF5F5;
            border-color: #FED7D7;
        }

        .izin {
            background-color: #FFFAF0;
            border-color: #FEEBC8;
        }

        .cutihutang {
            background-color: #F0FFF4;
            border-color: #C6F6D5;
        }

        .libur {
            background-color: #F8FAFC;
            border-color: #E2E8F0;
        }

        .absent {
            background-color: #FFF5F5;
            border-color: #FED7D7;
        }

        /* Shift Card Styling */
        .shift-card {
            padding: 12px;
        }

        .shift-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .shift-icon {
            font-size: 16px;
        }

        .shift-name {
            font-weight: 600;
            flex-grow: 1;
            color: #4C51BF;
        }

        .shift-date {
            font-size: 12px;
            color: #718096;
        }

        .shift-schedule {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            margin-bottom: 8px;
            border-bottom: 1px dashed #E2E8F0;
        }

        .shift-duration {
            background: #EDF2F7;
            color: #4C51BF;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .attendance-record {
            margin: 8px 0;
        }

        .attendance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
        }

        .attendance-time {
            font-weight: 500;
        }

        .attendance-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 8px;
        }

        /* Status colors */
        .late, .attendance-badge.late {
            color: #E53E3E;
            background-color: #FED7D7;
        }

        .early, .attendance-badge.early {
            color: #DD6B20;
            background-color: #FEEBC8;
        }

        .working {
            color: #D69E2E;
        }

        .attendance-badge.overtime {
            background-color: #EBF4FF;
            color: #4C51BF;
        }

        .lateleave, .attendance-badge.lateleave {
            background-color: #FEFCBF;
            color: #B7791F;
        }

        .attendance-badge.terlambat {
            background-color: #FED7D7;
            color: #E53E3E;
        }

        .overtime-info {
            font-size: 12px;
            padding: 8px;
            background: #F8FAFC;
            border-radius: 6px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .no-data {
            text-align: center;
            padding: 16px;
            background: #F8FAFC;
        }

        .working {
            background-color: #FFFAF0;
            border-color: #FEEBC8;
        }
    </style>
        `;
            }

            const mainShift = itemData;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const shiftDate = new Date(mainShift.Tanggal);
            shiftDate.setHours(0, 0, 0, 0);

            // Format time function
            const formatTime = (timeString) => {
                if (!timeString) return "-";
                const timePart = timeString.includes(" ")
                    ? timeString.split(" ")[1]
                    : timeString;
                return timePart ? timePart.substring(0, 5) : "-";
            };

            // Format date function
            const formatDate = (dateString) => {
                if (!dateString) return "";
                try {
                    const date = new Date(dateString);
                    return date.toLocaleDateString("id-ID", {
                        weekday: "short",
                        day: "2-digit",
                        month: "short",
                    });
                } catch (e) {
                    return dateString.split(" ")[0];
                }
            };

            // Check for leave/sick/absence cases first (priority)
            if (
                mainShift.Cuti_No_Transaksi ||
                mainShift.Sakit_No_Transaksi ||
                mainShift.Izin_No_Transaksi ||
                mainShift.CutiHutang_No_Transaksi
            ) {
                let type = "Izin";
                let icon = "📝";
                let statusClass = "izin";
                let startDate = mainShift.Izin_Mulai;
                let endDate = mainShift.Izin_Selesai;

                if (mainShift.Cuti_No_Transaksi) {
                    type = "Cuti";
                    icon = "🏖️";
                    statusClass = "cuti";
                    startDate = mainShift.Cuti_Mulai;
                    endDate = mainShift.Cuti_Selesai;
                } else if (mainShift.Sakit_No_Transaksi) {
                    type = "Sakit";
                    icon = "🏥";
                    statusClass = "sakit";
                    startDate = mainShift.Sakit_Mulai;
                    endDate = mainShift.Sakit_Selesai;
                } else if (mainShift.CutiHutang_No_Transaksi) {
                    type = "Cuti Hutang";
                    icon = "⏳";
                    statusClass = "cutihutang";
                    startDate = mainShift.CutiHutang_Mulai;
                    endDate = mainShift.CutiHutang_Selesai;
                }

                const formattedStart = formatDate(startDate);
                const formattedEnd = formatDate(endDate);

                return `
        <div class="dashboard-tooltip status-card ${statusClass}">
            <div class="status-header">
                <span class="status-icon">${icon}</span>
                <span class="status-title">${type}</span>
            </div>
            <div class="status-dates">${formattedStart}${
                    formattedEnd !== formattedStart ? ` - ${formattedEnd}` : ""
                }</div>
            ${
                mainShift.Cuti_No_Transaksi
                    ? '<div class="status-footer">Cuti</div>'
                    : ""
            }
            ${
                mainShift.Sakit_No_Transaksi
                    ? '<div class="status-footer">Sakit</div>'
                    : ""
            }
            ${
                mainShift.CutiHutang_No_Transaksi
                    ? '<div class="status-footer">Cuti hutang</div>'
                    : ""
            }
            ${
                mainShift.Izin_No_Transaksi
                    ? '<div class="status-footer">Izin pribadi</div>'
                    : ""
            }
        </div>
         <style>
        .dashboard-tooltip {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            max-width: 240px;
            padding: 12px;
            border-radius: 10px;
            background: #FFFFFF;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            font-size: 13px;
            color: #2D3748;
            border: 1px solid #E2E8F0;
        }

        /* Status Card Styling */
        .status-card {
            text-align: center;
            padding: 12px 16px;
        }

        .status-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .status-icon {
            font-size: 18px;
        }

        .status-title {
            font-weight: 600;
            font-size: 14px;
            color: #2D3748;
        }

        .status-dates {
            font-size: 12px;
            color: #718096;
            margin-bottom: 6px;
        }

        .status-footer {
            font-size: 11px;
            color: #718096;
            padding-top: 4px;
            border-top: 1px dashed #E2E8F0;
        }

        /* Status-specific colors */
        .cuti {
            background-color: #EBF8FF;
            border-color: #BEE3F8;
        }

        .sakit {
            background-color: #FFF5F5;
            border-color: #FED7D7;
        }

        .izin {
            background-color: #FFFAF0;
            border-color: #FEEBC8;
        }

        .cutihutang {
            background-color: #F0FFF4;
            border-color: #C6F6D5;
        }

        .libur {
            background-color: #F8FAFC;
            border-color: #E2E8F0;
        }

        .absent {
            background-color: #FFF5F5;
            border-color: #FED7D7;
        }

        /* Shift Card Styling */
        .shift-card {
            padding: 12px;
        }

        .shift-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .shift-icon {
            font-size: 16px;
        }

        .shift-name {
            font-weight: 600;
            flex-grow: 1;
            color: #4C51BF;
        }

        .shift-date {
            font-size: 12px;
            color: #718096;
        }

        .shift-schedule {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            margin-bottom: 8px;
            border-bottom: 1px dashed #E2E8F0;
        }

        .shift-duration {
            background: #EDF2F7;
            color: #4C51BF;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .attendance-record {
            margin: 8px 0;
        }

        .attendance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
        }

        .attendance-time {
            font-weight: 500;
        }

        .attendance-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 8px;
        }

        /* Status colors */
        .late, .attendance-badge.late {
            color: #E53E3E;
            background-color: #FED7D7;
        }

        .early, .attendance-badge.early {
            color: #DD6B20;
            background-color: #FEEBC8;
        }

        .working {
            color: #D69E2E;
        }

        .attendance-badge.overtime {
            background-color: #EBF4FF;
            color: #4C51BF;
        }

        .lateleave, .attendance-badge.lateleave {
            background-color: #FEFCBF;
            color: #B7791F;
        }

        .attendance-badge.terlambat {
            background-color: #FED7D7;
            color: #E53E3E;
        }

        .overtime-info {
            font-size: 12px;
            padding: 8px;
            background: #F8FAFC;
            border-radius: 6px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .no-data {
            text-align: center;
            padding: 16px;
            background: #F8FAFC;
        }

        .working {
            background-color: #FFFAF0;
            border-color: #FEEBC8;
        }
    </style>`;
            }

            // Handle NO SHIFT case
            if (!mainShift.Nama_Shift || mainShift.Nama_Shift === "NO SHIFT") {
                return `
        <div class="dashboard-tooltip status-card libur">
            <div class="status-header">
                <span class="status-icon">🌴</span>
                <span class="status-title">Hari Libur</span>
            </div>
            <div class="status-dates">${formatDate(mainShift.Tanggal)}</div>
        </div>
         <style>
        .dashboard-tooltip {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            max-width: 240px;
            padding: 12px;
            border-radius: 10px;
            background: #FFFFFF;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            font-size: 13px;
            color: #2D3748;
            border: 1px solid #E2E8F0;
        }

        /* Status Card Styling */
        .status-card {
            text-align: center;
            padding: 12px 16px;
        }

        .status-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .status-icon {
            font-size: 18px;
        }

        .status-title {
            font-weight: 600;
            font-size: 14px;
            color: #2D3748;
        }

        .status-dates {
            font-size: 12px;
            color: #718096;
            margin-bottom: 6px;
        }

        .status-footer {
            font-size: 11px;
            color: #718096;
            padding-top: 4px;
            border-top: 1px dashed #E2E8F0;
        }

        /* Status-specific colors */
        .cuti {
            background-color: #EBF8FF;
            border-color: #BEE3F8;
        }

        .sakit {
            background-color: #FFF5F5;
            border-color: #FED7D7;
        }

        .izin {
            background-color: #FFFAF0;
            border-color: #FEEBC8;
        }

        .cutihutang {
            background-color: #F0FFF4;
            border-color: #C6F6D5;
        }

        .libur {
            background-color: #F8FAFC;
            border-color: #E2E8F0;
        }

        .absent {
            background-color: #FFF5F5;
            border-color: #FED7D7;
        }

        /* Shift Card Styling */
        .shift-card {
            padding: 12px;
        }

        .shift-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .shift-icon {
            font-size: 16px;
        }

        .shift-name {
            font-weight: 600;
            flex-grow: 1;
            color: #4C51BF;
        }

        .shift-date {
            font-size: 12px;
            color: #718096;
        }

        .shift-schedule {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            margin-bottom: 8px;
            border-bottom: 1px dashed #E2E8F0;
        }

        .shift-duration {
            background: #EDF2F7;
            color: #4C51BF;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .attendance-record {
            margin: 8px 0;
        }

        .attendance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
        }

        .attendance-time {
            font-weight: 500;
        }

        .attendance-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 8px;
        }

        /* Status colors */
        .late, .attendance-badge.late {
            color: #E53E3E;
            background-color: #FED7D7;
        }

        .early, .attendance-badge.early {
            color: #DD6B20;
            background-color: #FEEBC8;
        }

        .working {
            color: #D69E2E;
        }

        .attendance-badge.overtime {
            background-color: #EBF4FF;
            color: #4C51BF;
        }

        .lateleave, .attendance-badge.lateleave {
            background-color: #FEFCBF;
            color: #B7791F;
        }

        .attendance-badge.terlambat {
            background-color: #FED7D7;
            color: #E53E3E;
        }

        .overtime-info {
            font-size: 12px;
            padding: 8px;
            background: #F8FAFC;
            border-radius: 6px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .no-data {
            text-align: center;
            padding: 16px;
            background: #F8FAFC;
        }

        .working {
            background-color: #FFFAF0;
            border-color: #FEEBC8;
        }
    </style>`;
            }

            // Check if future date
            const isFutureDate = shiftDate > today;

            // Check if user didn't check in (null CheckIn) and it's today or past date
            if (mainShift.CheckIn === null && !isFutureDate) {
                return `
        <div class="dashboard-tooltip status-card absent">
            <div class="status-header">
                <span class="status-icon">❌</span>
                <span class="status-title">Tidak Masuk</span>
            </div>
            <div class="shift-schedule">
                <span>Shift ${mainShift.Nama_Shift}</span>
                <span>${mainShift.Jam_Masuk} - ${mainShift.Jam_Keluar}</span>
            </div>
            <div class="status-dates">${formatDate(mainShift.Tanggal)}</div>
        </div>
         <style>
        .dashboard-tooltip {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            max-width: 240px;
            padding: 12px;
            border-radius: 10px;
            background: #FFFFFF;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            font-size: 13px;
            color: #2D3748;
            border: 1px solid #E2E8F0;
        }

        /* Status Card Styling */
        .status-card {
            text-align: center;
            padding: 12px 16px;
        }

        .status-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .status-icon {
            font-size: 18px;
        }

        .status-title {
            font-weight: 600;
            font-size: 14px;
            color: #2D3748;
        }

        .status-dates {
            font-size: 12px;
            color: #718096;
            margin-bottom: 6px;
        }

        .status-footer {
            font-size: 11px;
            color: #718096;
            padding-top: 4px;
            border-top: 1px dashed #E2E8F0;
        }

        /* Status-specific colors */
        .cuti {
            background-color: #EBF8FF;
            border-color: #BEE3F8;
        }

        .sakit {
            background-color: #FFF5F5;
            border-color: #FED7D7;
        }

        .izin {
            background-color: #FFFAF0;
            border-color: #FEEBC8;
        }

        .cutihutang {
            background-color: #F0FFF4;
            border-color: #C6F6D5;
        }

        .libur {
            background-color: #F8FAFC;
            border-color: #E2E8F0;
        }

        .absent {
            background-color: #FFF5F5;
            border-color: #FED7D7;
        }

        /* Shift Card Styling */
        .shift-card {
            padding: 12px;
        }

        .shift-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .shift-icon {
            font-size: 16px;
        }

        .shift-name {
            font-weight: 600;
            flex-grow: 1;
            color: #4C51BF;
        }

        .shift-date {
            font-size: 12px;
            color: #718096;
        }

        .shift-schedule {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            margin-bottom: 8px;
            border-bottom: 1px dashed #E2E8F0;
        }

        .shift-duration {
            background: #EDF2F7;
            color: #4C51BF;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .attendance-record {
            margin: 8px 0;
        }

        .attendance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
        }

        .attendance-time {
            font-weight: 500;
        }

        .attendance-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 8px;
        }

        /* Status colors */
        .late, .attendance-badge.late {
            color: #E53E3E;
            background-color: #FED7D7;
        }

        .early, .attendance-badge.early {
            color: #DD6B20;
            background-color: #FEEBC8;
        }

        .working {
            color: #D69E2E;
        }

        .attendance-badge.overtime {
            background-color: #EBF4FF;
            color: #4C51BF;
        }

        .lateleave, .attendance-badge.lateleave {
            background-color: #FEFCBF;
            color: #B7791F;
        }

        .attendance-badge.terlambat {
            background-color: #FED7D7;
            color: #E53E3E;
        }

        .overtime-info {
            font-size: 12px;
            padding: 8px;
            background: #F8FAFC;
            border-radius: 6px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .no-data {
            text-align: center;
            padding: 16px;
            background: #F8FAFC;
        }

        .working {
            background-color: #FFFAF0;
            border-color: #FEEBC8;
        }
    </style>`;
            }

            // Regular shift case
            const isLate =
                mainShift.Jam_Masuk &&
                mainShift.CheckIn &&
                new Date(`1970-01-01T${formatTime(mainShift.CheckIn)}`) >
                    new Date(`1970-01-01T${mainShift.Jam_Masuk}`) &&
                !mainShift.Terlambat_No_Transaksi;

            const isEarlyOut =
                mainShift.Jam_Keluar &&
                mainShift.CheckOut &&
                new Date(`1970-01-01T${formatTime(mainShift.CheckOut)}`) <
                    new Date(`1970-01-01T${mainShift.Jam_Keluar}`) &&
                !mainShift.Pulang_No_Transaksi;

            const isLateLeave = mainShift.Pulang_No_Transaksi;
            const hasOvertime = mainShift.Lembur_No_Transaksi;
            const isWorking =
                mainShift.CheckIn &&
                !mainShift.CheckOut &&
                shiftDate.getTime() === today.getTime();

            return `
    <div class="dashboard-tooltip shift-card ${isWorking ? "working" : ""}">
        <div class="shift-header">
            <span class="shift-icon">${isWorking ? "🔄" : "🕒"}</span>
            <span class="shift-name">${mainShift.Nama_Shift}</span>
            <span class="shift-date">${formatDate(mainShift.Tanggal)}</span>
        </div>

        <div class="shift-schedule">
            <span>${mainShift.Jam_Masuk} - ${mainShift.Jam_Keluar}</span>
            <span class="shift-duration">${this.calculateWorkHours(
                mainShift.Jam_Masuk,
                mainShift.Jam_Keluar
            )} jam</span>
        </div>

        <div class="attendance-record">
            <div class="attendance-row ${isLate ? "late" : ""}">
                <span>Masuk:</span>
                <span class="attendance-time">${
                    mainShift.CheckIn ? formatTime(mainShift.CheckIn) : "-"
                }</span>
                ${
                    isLate
                        ? '<span class="attendance-badge late">Telat</span>'
                        : ""
                }
                ${
                    mainShift.Terlambat_No_Transaksi
                        ? '<span class="attendance-badge terlambat">Izin Terlambat</span>'
                        : ""
                }
            </div>

            ${
                mainShift.CheckOut
                    ? `
            <div class="attendance-row ${isEarlyOut ? "early" : ""} ${
                          isLateLeave ? "lateleave" : ""
                      }">
                <span>Pulang:</span>
                <span class="attendance-time">${formatTime(
                    mainShift.CheckOut
                )}</span>
                ${
                    isEarlyOut
                        ? '<span class="attendance-badge early">Awal</span>'
                        : ""
                }
                ${
                    isLateLeave
                        ? '<span class="attendance-badge lateleave">Izin Awal</span>'
                        : ""
                }
                ${
                    hasOvertime
                        ? '<span class="attendance-badge overtime">Lembur</span>'
                        : ""
                }
            </div>
            `
                    : `
            <div class="attendance-row working">
                <span>Pulang:</span>
                <span class="attendance-time">${
                    isFutureDate ? "Belum Dilakukan" : "Belum Check Out"
                }</span>
            </div>
            `
            }
        </div>

        ${
            hasOvertime
                ? `
        <div class="overtime-info">
            <span>⏰ Lembur: ${formatTime(
                mainShift.Lembur_Mulai
            )} - ${formatTime(mainShift.Lembur_Selesai)}</span>
        </div>
        `
                : ""
        }
    </div>

    <style>
        .dashboard-tooltip {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            max-width: 240px;
            padding: 12px;
            border-radius: 10px;
            background: #FFFFFF;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            font-size: 13px;
            color: #2D3748;
            border: 1px solid #E2E8F0;
        }

        /* Status Card Styling */
        .status-card {
            text-align: center;
            padding: 12px 16px;
        }

        .status-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 6px;
        }

        .status-icon {
            font-size: 18px;
        }

        .status-title {
            font-weight: 600;
            font-size: 14px;
            color: #2D3748;
        }

        .status-dates {
            font-size: 12px;
            color: #718096;
            margin-bottom: 6px;
        }

        .status-footer {
            font-size: 11px;
            color: #718096;
            padding-top: 4px;
            border-top: 1px dashed #E2E8F0;
        }

        /* Status-specific colors */
        .cuti {
            background-color: #EBF8FF;
            border-color: #BEE3F8;
        }

        .sakit {
            background-color: #FFF5F5;
            border-color: #FED7D7;
        }

        .izin {
            background-color: #FFFAF0;
            border-color: #FEEBC8;
        }

        .cutihutang {
            background-color: #F0FFF4;
            border-color: #C6F6D5;
        }

        .libur {
            background-color: #F8FAFC;
            border-color: #E2E8F0;
        }

        .absent {
            background-color: #FFF5F5;
            border-color: #FED7D7;
        }

        /* Shift Card Styling */
        .shift-card {
            padding: 12px;
        }

        .shift-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .shift-icon {
            font-size: 16px;
        }

        .shift-name {
            font-weight: 600;
            flex-grow: 1;
            color: #4C51BF;
        }

        .shift-date {
            font-size: 12px;
            color: #718096;
        }

        .shift-schedule {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            margin-bottom: 8px;
            border-bottom: 1px dashed #E2E8F0;
        }

        .shift-duration {
            background: #EDF2F7;
            color: #4C51BF;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .attendance-record {
            margin: 8px 0;
        }

        .attendance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
        }

        .attendance-time {
            font-weight: 500;
        }

        .attendance-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 12px;
            margin-left: 8px;
        }

        /* Status colors */
        .late, .attendance-badge.late {
            color: #E53E3E;
            background-color: #FED7D7;
            border-radius:12px;
            padding: 3px 5px;
        }

        .early, .attendance-badge.early {
            color: #DD6B20;
            background-color: #FEEBC8;
        }

        .working {
            color: #D69E2E;
        }

        .attendance-badge.overtime {
            background-color: #EBF4FF;
            color: #4C51BF;
        }

        .lateleave, .attendance-badge.lateleave {
            background-color: #FEFCBF;
            color: #B7791F;
            border-radius:12px;
            padding: 3px 3px
        }

        .attendance-badge.terlambat {
            background-color: #FED7D7;
            color: #E53E3E;
        }

        .overtime-info {
            font-size: 12px;
            padding: 8px;
            background: #F8FAFC;
            border-radius: 6px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .no-data {
            text-align: center;
            padding: 16px;
            background: #F8FAFC;
        }

        .working {
            background-color: #FFFAF0;
            border-color: #FEEBC8;
        }
    </style>
    `;
        },
        //     getTooltipContent(itemData) {
        //         // Check if itemData exists
        //         if (!itemData) {
        //             return `
        //     <div class="dashboard-tooltip no-data">
        //         <div class="tooltip-content">
        //             <span class="status-icon">ℹ️</span>
        //             <span>No shift data available</span>
        //         </div>
        //     </div>
        //     <style>
        //     .dashboard-tooltip {
        //         font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        //         max-width: 240px;
        //         padding: 12px;
        //         border-radius: 10px;
        //         background: #FFFFFF;
        //         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        //         font-size: 13px;
        //         color: #2D3748;
        //         border: 1px solid #E2E8F0;
        //     }

        //     /* Status Card Styling */
        //     .status-card {
        //         text-align: center;
        //         padding: 12px 16px;
        //     }

        //     .status-header {
        //         display: flex;
        //         align-items: center;
        //         justify-content: center;
        //         gap: 8px;
        //         margin-bottom: 6px;
        //     }

        //     .status-icon {
        //         font-size: 18px;
        //     }

        //     .status-title {
        //         font-weight: 600;
        //         font-size: 14px;
        //         color: #2D3748;
        //     }

        //     .status-dates {
        //         font-size: 12px;
        //         color: #718096;
        //         margin-bottom: 6px;
        //     }

        //     .status-footer {
        //         font-size: 11px;
        //         color: #718096;
        //         padding-top: 4px;
        //         border-top: 1px dashed #E2E8F0;
        //     }

        //     .absent {
        //         background-color: #FFF5F5;
        //         border-color: #FED7D7;
        //     }

        //     /* Shift Card Styling */
        //     .shift-card {
        //         padding: 12px;
        //     }

        //     .shift-header {
        //         display: flex;
        //         align-items: center;
        //         gap: 8px;
        //         margin-bottom: 10px;
        //     }

        //     .shift-icon {
        //         font-size: 16px;
        //     }

        //     .shift-name {
        //         font-weight: 600;
        //         flex-grow: 1;
        //         color: #4C51BF;
        //     }

        //     .shift-date {
        //         font-size: 12px;
        //         color: #718096;
        //     }

        //     .shift-schedule {
        //         display: flex;
        //         justify-content: space-between;
        //         align-items: center;
        //         padding: 8px 0;
        //         margin-bottom: 8px;
        //         border-bottom: 1px dashed #E2E8F0;
        //     }

        //     .shift-duration {
        //         background: #EDF2F7;
        //         color: #4C51BF;
        //         padding: 2px 8px;
        //         border-radius: 12px;
        //         font-size: 11px;
        //         font-weight: 600;
        //     }

        //     .attendance-record {
        //         margin: 8px 0;
        //     }

        //     .attendance-row {
        //         display: flex;
        //         justify-content: space-between;
        //         align-items: center;
        //         padding: 6px 0;
        //     }

        //     .attendance-time {
        //         font-weight: 500;
        //     }

        //     .attendance-badge {
        //         font-size: 11px;
        //         font-weight: 600;
        //         padding: 2px 8px;
        //         border-radius: 12px;
        //         margin-left: 8px;
        //     }

        //     .late {
        //         color: #E53E3E;
        //     }

        //     .attendance-badge.late {
        //         background-color: #FED7D7;
        //         color: #E53E3E;
        //     }

        //     .early {
        //         color: #DD6B20;
        //     }

        //     .attendance-badge.early {
        //         background-color: #FEEBC8;
        //         color: #DD6B20;
        //     }

        //     .working {
        //         color: #D69E2E;
        //     }

        //     .attendance-badge.overtime {
        //         background-color: #EBF4FF;
        //         color: #4C51BF;
        //     }

        //     .attendance-badge.lateleave {
        //         background-color: #FEFCBF;
        //         color: #B7791F;
        //     }

        //     .overtime-info {
        //         font-size: 12px;
        //         padding: 8px;
        //         background: #F8FAFC;
        //         border-radius: 6px;
        //         margin-top: 8px;
        //         display: flex;
        //         align-items: center;
        //         gap: 6px;
        //     }

        //     .no-data {
        //         text-align: center;
        //         padding: 16px;
        //         background: #F8FAFC;
        //     }
        // </style>
        //     `;
        //         }

        //         const mainShift = itemData;
        //         const today = new Date();
        //         today.setHours(0, 0, 0, 0);
        //         const shiftDate = new Date(mainShift.Tanggal);
        //         // shiftDate.setHours(0, 0, 0, 0);

        //         // Format time function
        //         const formatTime = (timeString) => {
        //             if (!timeString) return "-";
        //             const timePart = timeString.includes(" ")
        //                 ? timeString.split(" ")[1]
        //                 : timeString;
        //             return timePart ? timePart.substring(0, 5) : "-";
        //         };

        //         // Format date function
        //         const formatDate = (dateString) => {
        //             if (!dateString) return "";
        //             try {
        //                 const date = new Date(dateString);
        //                 return date.toLocaleDateString("id-ID", {
        //                     weekday: "short",
        //                     day: "2-digit",
        //                     month: "short",
        //                 });
        //             } catch (e) {
        //                 return dateString.split(" ")[0];
        //             }
        //         };

        //         // Check for leave/sick first (priority)
        //         if (
        //             mainShift.Cuti_No_Transaksi ||
        //             mainShift.Sakit_Izin_No_Transaksi
        //         ) {
        //             let type = "Izin";
        //             let icon = "📝";

        //             if (mainShift.Cuti_No_Transaksi) {
        //                 type = "Cuti";
        //                 icon = "🏖️";
        //             } else if (mainShift.Sakit_Izin_No_Transaksi) {
        //                 type = "Sakit";
        //                 icon = "🏥";
        //             }

        //             const startDate = formatDate(
        //                 mainShift.Cuti_Mulai || mainShift.Sakit_Izin_Mulai
        //             );
        //             const endDate = formatDate(
        //                 mainShift.Cuti_Selesai || mainShift.Sakit_Izin_Selesai
        //             );

        //             return `
        //     <div class="dashboard-tooltip status-card">
        //         <div class="status-header">
        //             <span class="status-icon">${icon}</span>
        //             <span class="status-title">${type}</span>
        //         </div>
        //         <div class="status-dates">${startDate} - ${endDate}</div>
        //         ${
        //             mainShift.Cuti_No_Transaksi
        //                 ? '<div class="status-footer">Cuti resmi</div>'
        //                 : ""
        //         }
        //         ${
        //             mainShift.Sakit_Izin_No_Transaksi
        //                 ? '<div class="status-footer">Surat sakit</div>'
        //                 : ""
        //         }
        //     </div>
        //     <style>
        //     .dashboard-tooltip {
        //         font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        //         max-width: 240px;
        //         padding: 12px;
        //         border-radius: 10px;
        //         background: #FFFFFF;
        //         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        //         font-size: 13px;
        //         color: #2D3748;
        //         border: 1px solid #E2E8F0;
        //     }

        //     /* Status Card Styling */
        //     .status-card {
        //         text-align: center;
        //         padding: 12px 16px;
        //     }

        //     .status-header {
        //         display: flex;
        //         align-items: center;
        //         justify-content: center;
        //         gap: 8px;
        //         margin-bottom: 6px;
        //     }

        //     .status-icon {
        //         font-size: 18px;
        //     }

        //     .status-title {
        //         font-weight: 600;
        //         font-size: 14px;
        //         color: #2D3748;
        //     }

        //     .status-dates {
        //         font-size: 12px;
        //         color: #718096;
        //         margin-bottom: 6px;
        //     }

        //     .status-footer {
        //         font-size: 11px;
        //         color: #718096;
        //         padding-top: 4px;
        //         border-top: 1px dashed #E2E8F0;
        //     }

        //     .absent {
        //         background-color: #FFF5F5;
        //         border-color: #FED7D7;
        //     }

        //     /* Shift Card Styling */
        //     .shift-card {
        //         padding: 12px;
        //     }

        //     .shift-header {
        //         display: flex;
        //         align-items: center;
        //         gap: 8px;
        //         margin-bottom: 10px;
        //     }

        //     .shift-icon {
        //         font-size: 16px;
        //     }

        //     .shift-name {
        //         font-weight: 600;
        //         flex-grow: 1;
        //         color: #4C51BF;
        //     }

        //     .shift-date {
        //         font-size: 12px;
        //         color: #718096;
        //     }

        //     .shift-schedule {
        //         display: flex;
        //         justify-content: space-between;
        //         align-items: center;
        //         padding: 8px 0;
        //         margin-bottom: 8px;
        //         border-bottom: 1px dashed #E2E8F0;
        //     }

        //     .shift-duration {
        //         background: #EDF2F7;
        //         color: #4C51BF;
        //         padding: 2px 8px;
        //         border-radius: 12px;
        //         font-size: 11px;
        //         font-weight: 600;
        //     }

        //     .attendance-record {
        //         margin: 8px 0;
        //     }

        //     .attendance-row {
        //         display: flex;
        //         justify-content: space-between;
        //         align-items: center;
        //         padding: 6px 0;
        //     }

        //     .attendance-time {
        //         font-weight: 500;
        //     }

        //     .attendance-badge {
        //         font-size: 11px;
        //         font-weight: 600;
        //         padding: 2px 8px;
        //         border-radius: 12px;
        //         margin-left: 8px;
        //     }

        //     .late {
        //         color: #E53E3E;
        //     }

        //     .attendance-badge.late {
        //         background-color: #FED7D7;
        //         color: #E53E3E;
        //     }

        //     .early {
        //         color: #DD6B20;
        //     }

        //     .attendance-badge.early {
        //         background-color: #FEEBC8;
        //         color: #DD6B20;
        //     }

        //     .working {
        //         color: #D69E2E;
        //     }

        //     .attendance-badge.overtime {
        //         background-color: #EBF4FF;
        //         color: #4C51BF;
        //     }

        //     .attendance-badge.lateleave {
        //         background-color: #FEFCBF;
        //         color: #B7791F;
        //     }

        //     .overtime-info {
        //         font-size: 12px;
        //         padding: 8px;
        //         background: #F8FAFC;
        //         border-radius: 6px;
        //         margin-top: 8px;
        //         display: flex;
        //         align-items: center;
        //         gap: 6px;
        //     }

        //     .no-data {
        //         text-align: center;
        //         padding: 16px;
        //         background: #F8FAFC;
        //     }
        // </style>
        //     `;
        //         }

        //         // Handle NO SHIFT case
        //         if (!mainShift.Nama_Shift || mainShift.Nama_Shift === "NO SHIFT") {
        //             return `
        //     <div class="dashboard-tooltip status-card">
        //         <div class="status-header">
        //             <span class="status-icon">🌴</span>
        //             <span class="status-title">Hari Libur</span>
        //         </div>
        //         <div class="status-dates">${formatDate(mainShift.Tanggal)}</div>
        //     </div>
        //     <style>
        //     .dashboard-tooltip {
        //         font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        //         max-width: 240px;
        //         padding: 12px;
        //         border-radius: 10px;
        //         background: #FFFFFF;
        //         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        //         font-size: 13px;
        //         color: #2D3748;
        //         border: 1px solid #E2E8F0;
        //     }

        //     /* Status Card Styling */
        //     .status-card {
        //         text-align: center;
        //         padding: 12px 16px;
        //     }

        //     .status-header {
        //         display: flex;
        //         align-items: center;
        //         justify-content: center;
        //         gap: 8px;
        //         margin-bottom: 6px;
        //     }

        //     .status-icon {
        //         font-size: 18px;
        //     }

        //     .status-title {
        //         font-weight: 600;
        //         font-size: 14px;
        //         color: #2D3748;
        //     }

        //     .status-dates {
        //         font-size: 12px;
        //         color: #718096;
        //         margin-bottom: 6px;
        //     }

        //     .status-footer {
        //         font-size: 11px;
        //         color: #718096;
        //         padding-top: 4px;
        //         border-top: 1px dashed #E2E8F0;
        //     }

        //     .absent {
        //         background-color: #FFF5F5;
        //         border-color: #FED7D7;
        //     }

        //     /* Shift Card Styling */
        //     .shift-card {
        //         padding: 12px;
        //     }

        //     .shift-header {
        //         display: flex;
        //         align-items: center;
        //         gap: 8px;
        //         margin-bottom: 10px;
        //     }

        //     .shift-icon {
        //         font-size: 16px;
        //     }

        //     .shift-name {
        //         font-weight: 600;
        //         flex-grow: 1;
        //         color: #4C51BF;
        //     }

        //     .shift-date {
        //         font-size: 12px;
        //         color: #718096;
        //     }

        //     .shift-schedule {
        //         display: flex;
        //         justify-content: space-between;
        //         align-items: center;
        //         padding: 8px 0;
        //         margin-bottom: 8px;
        //         border-bottom: 1px dashed #E2E8F0;
        //     }

        //     .shift-duration {
        //         background: #EDF2F7;
        //         color: #4C51BF;
        //         padding: 2px 8px;
        //         border-radius: 12px;
        //         font-size: 11px;
        //         font-weight: 600;
        //     }

        //     .attendance-record {
        //         margin: 8px 0;
        //     }

        //     .attendance-row {
        //         display: flex;
        //         justify-content: space-between;
        //         align-items: center;
        //         padding: 6px 0;
        //     }

        //     .attendance-time {
        //         font-weight: 500;
        //     }

        //     .attendance-badge {
        //         font-size: 11px;
        //         font-weight: 600;
        //         padding: 2px 8px;
        //         border-radius: 12px;
        //         margin-left: 8px;
        //     }

        //     .late {
        //         color: #E53E3E;
        //     }

        //     .attendance-badge.late {
        //         background-color: #FED7D7;
        //         color: #E53E3E;
        //     }

        //     .early {
        //         color: #DD6B20;
        //     }

        //     .attendance-badge.early {
        //         background-color: #FEEBC8;
        //         color: #DD6B20;
        //     }

        //     .working {
        //         color: #D69E2E;
        //     }

        //     .attendance-badge.overtime {
        //         background-color: #EBF4FF;
        //         color: #4C51BF;
        //     }

        //     .attendance-badge.lateleave {
        //         background-color: #FEFCBF;
        //         color: #B7791F;
        //     }

        //     .overtime-info {
        //         font-size: 12px;
        //         padding: 8px;
        //         background: #F8FAFC;
        //         border-radius: 6px;
        //         margin-top: 8px;
        //         display: flex;
        //         align-items: center;
        //         gap: 6px;
        //     }

        //     .no-data {
        //         text-align: center;
        //         padding: 16px;
        //         background: #F8FAFC;
        //     }
        // </style>
        //     `;
        //         }

        //         // Check if future date
        //         const isFutureDate = shiftDate > today;

        //         // Check if user didn't check in (null CheckIn) and it's today or past date
        //         if (mainShift.CheckIn === null && !isFutureDate) {
        //             return `
        //     <div class="dashboard-tooltip status-card absent">
        //         <div class="status-header">
        //             <span class="status-icon">❌</span>
        //             <span class="status-title">Tidak Masuk</span>
        //         </div>
        //         <div class="shift-schedule">
        //             <span>Shift ${mainShift.Nama_Shift}</span>
        //             <span>${mainShift.Jam_Masuk} - ${mainShift.Jam_Keluar}</span>
        //         </div>
        //         <div class="status-dates">${formatDate(mainShift.Tanggal)}</div>
        //     </div>
        //     <style>
        //     .dashboard-tooltip {
        //         font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        //         max-width: 240px;
        //         padding: 12px;
        //         border-radius: 10px;
        //         background: #FFFFFF;
        //         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        //         font-size: 13px;
        //         color: #2D3748;
        //         border: 1px solid #E2E8F0;
        //     }

        //     /* Status Card Styling */
        //     .status-card {
        //         text-align: center;
        //         padding: 12px 16px;
        //     }

        //     .status-header {
        //         display: flex;
        //         align-items: center;
        //         justify-content: center;
        //         gap: 8px;
        //         margin-bottom: 6px;
        //     }

        //     .status-icon {
        //         font-size: 18px;
        //     }

        //     .status-title {
        //         font-weight: 600;
        //         font-size: 14px;
        //         color: #2D3748;
        //     }

        //     .status-dates {
        //         font-size: 12px;
        //         color: #718096;
        //         margin-bottom: 6px;
        //     }

        //     .status-footer {
        //         font-size: 11px;
        //         color: #718096;
        //         padding-top: 4px;
        //         border-top: 1px dashed #E2E8F0;
        //     }

        //     .absent {
        //         background-color: #FFF5F5;
        //         border-color: #FED7D7;
        //     }

        //     /* Shift Card Styling */
        //     .shift-card {
        //         padding: 12px;
        //     }

        //     .shift-header {
        //         display: flex;
        //         align-items: center;
        //         gap: 8px;
        //         margin-bottom: 10px;
        //     }

        //     .shift-icon {
        //         font-size: 16px;
        //     }

        //     .shift-name {
        //         font-weight: 600;
        //         flex-grow: 1;
        //         color: #4C51BF;
        //     }

        //     .shift-date {
        //         font-size: 12px;
        //         color: #718096;
        //     }

        //     .shift-schedule {
        //         display: flex;
        //         justify-content: space-between;
        //         align-items: center;
        //         padding: 8px 0;
        //         margin-bottom: 8px;
        //         border-bottom: 1px dashed #E2E8F0;
        //     }

        //     .shift-duration {
        //         background: #EDF2F7;
        //         color: #4C51BF;
        //         padding: 2px 8px;
        //         border-radius: 12px;
        //         font-size: 11px;
        //         font-weight: 600;
        //     }

        //     .attendance-record {
        //         margin: 8px 0;
        //     }

        //     .attendance-row {
        //         display: flex;
        //         justify-content: space-between;
        //         align-items: center;
        //         padding: 6px 0;
        //     }

        //     .attendance-time {
        //         font-weight: 500;
        //     }

        //     .attendance-badge {
        //         font-size: 11px;
        //         font-weight: 600;
        //         padding: 2px 8px;
        //         border-radius: 12px;
        //         margin-left: 8px;
        //     }

        //     .late {
        //         color: #E53E3E;
        //     }

        //     .attendance-badge.late {
        //         background-color: #FED7D7;
        //         color: #E53E3E;
        //     }

        //     .early {
        //         color: #DD6B20;
        //     }

        //     .attendance-badge.early {
        //         background-color: #FEEBC8;
        //         color: #DD6B20;
        //     }

        //     .working {
        //         color: #D69E2E;
        //     }

        //     .attendance-badge.overtime {
        //         background-color: #EBF4FF;
        //         color: #4C51BF;
        //     }

        //     .attendance-badge.lateleave {
        //         background-color: #FEFCBF;
        //         color: #B7791F;
        //     }

        //     .overtime-info {
        //         font-size: 12px;
        //         padding: 8px;
        //         background: #F8FAFC;
        //         border-radius: 6px;
        //         margin-top: 8px;
        //         display: flex;
        //         align-items: center;
        //         gap: 6px;
        //     }

        //     .no-data {
        //         text-align: center;
        //         padding: 16px;
        //         background: #F8FAFC;
        //     }
        // </style>
        //     `;
        //         }

        //         // Regular shift case
        //         const isLate =
        //             mainShift.Jam_Masuk &&
        //             mainShift.CheckIn &&
        //             new Date(`1970-01-01T${formatTime(mainShift.CheckIn)}`) >
        //                 new Date(`1970-01-01T${mainShift.Jam_Masuk}`);

        //         const isEarlyOut =
        //             mainShift.Jam_Keluar &&
        //             mainShift.CheckOut &&
        //             new Date(`1970-01-01T${formatTime(mainShift.CheckOut)}`) <
        //                 new Date(`1970-01-01T${mainShift.Jam_Keluar}`);

        //         const isWorking =
        //             mainShift.CheckIn &&
        //             !mainShift.CheckOut &&
        //             shiftDate.getTime() === today.getTime();

        //         // Check for overtime
        //         const hasOvertime = mainShift.Lembur_No_Transaksi;
        //         const isLateLeave = mainShift.PulangTerlambat_No_Transaksi;

        //         return `
        // <div class="dashboard-tooltip shift-card ${isWorking ? "working" : ""}">
        //     <div class="shift-header">
        //         <span class="shift-icon">${isWorking ? "🔄" : "🕒"}</span>
        //         <span class="shift-name">${mainShift.Nama_Shift}</span>
        //         <span class="shift-date">${formatDate(mainShift.Tanggal)}</span>
        //     </div>

        //     <div class="shift-schedule">
        //         <span>${mainShift.Jam_Masuk} - ${mainShift.Jam_Keluar}</span>
        //         <span class="shift-duration">${this.calculateWorkHours(
        //             mainShift.Jam_Masuk,
        //             mainShift.Jam_Keluar
        //         )} jam</span>
        //     </div>

        //     <div class="attendance-record">
        //         <div class="attendance-row ${isLate ? "late" : ""}">
        //             <span>Masuk:</span>
        //             <span class="attendance-time">${
        //                 mainShift.CheckIn ? formatTime(mainShift.CheckIn) : "-"
        //             }</span>
        //             ${
        //                 isLate
        //                     ? '<span class="attendance-badge late">Telat</span>'
        //                     : ""
        //             }
        //         </div>

        //         ${
        //             mainShift.CheckOut
        //                 ? `
        //         <div class="attendance-row ${isEarlyOut ? "early" : ""}">
        //             <span>Pulang:</span>
        //             <span class="attendance-time">${formatTime(
        //                 mainShift.CheckOut
        //             )}</span>
        //             ${
        //                 isEarlyOut
        //                     ? '<span class="attendance-badge early">Awal</span>'
        //                     : ""
        //             }
        //             ${
        //                 hasOvertime
        //                     ? '<span class="attendance-badge overtime">Lembur</span>'
        //                     : ""
        //             }
        //             ${
        //                 isLateLeave
        //                     ? '<span class="attendance-badge lateleave">Telat</span>'
        //                     : ""
        //             }
        //         </div>
        //         `
        //                 : `
        //         <div class="attendance-row working">
        //             <span>Pulang:</span>
        //             <span class="attendance-time">${
        //                 isFutureDate ? "Belum Dilakukan" : "Belum Check Out"
        //             }</span>
        //         </div>
        //         `
        //         }
        //     </div>

        //     ${
        //         hasOvertime
        //             ? `
        //     <div class="overtime-info">
        //         <span>⏰ Lembur: ${formatTime(
        //             mainShift.Lembur_Mulai
        //         )} - ${formatTime(mainShift.Lembur_Selesai)}</span>
        //     </div>
        //     `
        //             : ""
        //     }
        // </div>

        // <style>
        //     .dashboard-tooltip {
        //         font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        //         max-width: 240px;
        //         padding: 12px;
        //         border-radius: 10px;
        //         background: #FFFFFF;
        //         box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        //         font-size: 13px;
        //         color: #2D3748;
        //         border: 1px solid #E2E8F0;
        //     }

        //     /* Status Card Styling */
        //     .status-card {
        //         text-align: center;
        //         padding: 12px 16px;
        //     }

        //     .status-header {
        //         display: flex;
        //         align-items: center;
        //         justify-content: center;
        //         gap: 8px;
        //         margin-bottom: 6px;
        //     }

        //     .status-icon {
        //         font-size: 18px;
        //     }

        //     .status-title {
        //         font-weight: 600;
        //         font-size: 14px;
        //         color: #2D3748;
        //     }

        //     .status-dates {
        //         font-size: 12px;
        //         color: #718096;
        //         margin-bottom: 6px;
        //     }

        //     .status-footer {
        //         font-size: 11px;
        //         color: #718096;
        //         padding-top: 4px;
        //         border-top: 1px dashed #E2E8F0;
        //     }

        //     .absent {
        //         background-color: #FFF5F5;
        //         border-color: #FED7D7;
        //     }

        //     /* Shift Card Styling */
        //     .shift-card {
        //         padding: 12px;
        //     }

        //     .shift-header {
        //         display: flex;
        //         align-items: center;
        //         gap: 8px;
        //         margin-bottom: 10px;
        //     }

        //     .shift-icon {
        //         font-size: 16px;
        //     }

        //     .shift-name {
        //         font-weight: 600;
        //         flex-grow: 1;
        //         color: #4C51BF;
        //     }

        //     .shift-date {
        //         font-size: 12px;
        //         color: #718096;
        //     }

        //     .shift-schedule {
        //         display: flex;
        //         justify-content: space-between;
        //         align-items: center;
        //         padding: 8px 0;
        //         margin-bottom: 8px;
        //         border-bottom: 1px dashed #E2E8F0;
        //     }

        //     .shift-duration {
        //         background: #EDF2F7;
        //         color: #4C51BF;
        //         padding: 2px 8px;
        //         border-radius: 12px;
        //         font-size: 11px;
        //         font-weight: 600;
        //     }

        //     .attendance-record {
        //         margin: 8px 0;
        //     }

        //     .attendance-row {
        //         display: flex;
        //         justify-content: space-between;
        //         align-items: center;
        //         padding: 6px 0;
        //     }

        //     .attendance-time {
        //         font-weight: 500;
        //     }

        //     .attendance-badge {
        //         font-size: 11px;
        //         font-weight: 600;
        //         padding: 2px 8px;
        //         border-radius: 12px;
        //         margin-left: 8px;
        //     }

        //     .late {
        //         color: #E53E3E;
        //     }

        //     .attendance-badge.late {
        //         background-color: #FED7D7;
        //         color: #E53E3E;
        //     }

        //     .early {
        //         color: #DD6B20;
        //     }

        //     .attendance-badge.early {
        //         background-color: #FEEBC8;
        //         color: #DD6B20;
        //     }

        //     .working {
        //         color: #D69E2E;
        //     }

        //     .attendance-badge.overtime {
        //         background-color: #EBF4FF;
        //         color: #4C51BF;
        //     }

        //     .attendance-badge.lateleave {
        //         background-color: #FEFCBF;
        //         color: #B7791F;
        //     }

        //     .overtime-info {
        //         font-size: 12px;
        //         padding: 8px;
        //         background: #F8FAFC;
        //         border-radius: 6px;
        //         margin-top: 8px;
        //         display: flex;
        //         align-items: center;
        //         gap: 6px;
        //     }

        //     .no-data {
        //         text-align: center;
        //         padding: 16px;
        //         background: #F8FAFC;
        //     }
        // </style>
        // `;
        //     },

        calculateWorkHours(jamMasuk, jamKeluar) {
            if (!jamMasuk || !jamKeluar) return 0;

            const [masukHour, masukMin] = jamMasuk.split(":").map(Number);
            const [keluarHour, keluarMin] = jamKeluar.split(":").map(Number);

            const masukMinutes = masukHour * 60 + masukMin;
            const keluarMinutes = keluarHour * 60 + keluarMin;

            const durasiMinutes = keluarMinutes - masukMinutes;
            return Math.round((durasiMinutes / 60) * 10) / 10; // Round to 1 decimal place
        },
        async getDateAbsen() {
            try {
                this.loadingCalendar = true;

                const response = await axios.get("/uDash/getDateAbsen");
                if (response.status == 200) {
                    this.dataDateAbsen = response.data.data;
                    // console.log(this.dataDateAbsen);
                }
            } catch (error) {
                console.log(error);
            } finally {
                this.loadingCalendar = false;
            }
        },
        selectDay(day) {
            this.selectedDay = day;
        },
        shiftBadgeClass(day) {
            return {
                "bg-info": day.Nama_Shift === "NO SHIFT",
                "bg-warning":
                    day.Nama_Shift == "OG-K2" || day.Nama_Shift == "OG-K1",
                "bg-primary": day.Nama_Shift === "EMI*",
                "bg-secondary": day.Nama_Shift === "Day Off",
                "bg-primary": !(
                    day.Nama_Shift == "NO SHIFT" ||
                    day.Nama_Shift == "OG-K2" ||
                    day.Nama_Shift == "OG-K1" ||
                    day.Nama_Shift == "EMI*" ||
                    day.Nama_Shift == "Day Off"
                ),
            };
        },

        nextMonth() {
            const nextDate = new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth() + 1,
                1
            );
            this.currentDate = nextDate;
        },
        previousMonth() {
            const prevDate = new Date(
                this.currentDate.getFullYear(),
                this.currentDate.getMonth() - 1,
                1
            );
            this.currentDate = prevDate;
        },
        truncateText(text, length) {
            return text.length > length
                ? text.substring(0, length) + "..."
                : text;
        },
        formatTanggal_Lembur(start, end) {
            const [tanggalMulaiRaw] = start.split(" ");
            const [tanggalSelesaiRaw] = end.split(" ");

            const mulai = new Date(tanggalMulaiRaw);
            const selesai = new Date(tanggalSelesaiRaw);

            // Ambil komponen tanggal
            const dMulai = mulai.getDate();
            const mMulai = mulai.toLocaleString("id-ID", { month: "long" });
            const yMulai = mulai.getFullYear();

            const dSelesai = selesai.getDate();
            const mSelesai = selesai.toLocaleString("id-ID", { month: "long" });
            const ySelesai = selesai.getFullYear();

            // Kalau sama persis → tampilkan satu tanggal
            if (tanggalMulaiRaw === tanggalSelesaiRaw) {
                return `${dMulai} ${mMulai} ${yMulai}`;
            }

            // Kalau bulan & tahun sama → "28 - 29 Juni 2025"
            if (mMulai === mSelesai && yMulai === ySelesai) {
                return `${dMulai} - ${dSelesai} ${mMulai} ${yMulai}`;
            }

            // Kalau beda bulan/tahun → "28 Juni 2025 - 2 Juli 2025"
            return `${dMulai} ${mMulai} ${yMulai} - ${dSelesai} ${mSelesai} ${ySelesai}`;
        },
        parseIzin(tipe) {
            if (tipe == "izinFull") {
                return "Izin";
            } else if (tipe == "sakit") {
                return "Sakit";
            } else if (tipe == "pulangCepat") {
                return "Pulang Cepat";
            } else if (tipe == "terlambat") {
                return "Terlambat";
            } else if (tipe == "cuti") {
                return "Cuti";
            } else if (tipe == "cutiHutang") {
                return "Cuti";
            }
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
        formatTanggal_Izin(start, end) {
            const tanggalMulai = new Date(start);

            const tanggalRawMulai = tanggalMulai?.toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "long",
                year: "numeric",
            });
            if (!end) {
                return `${tanggalRawMulai}`;
            }
            const tanggalEnd = new Date(end);

            const tanggalRawEnd = tanggalEnd?.toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "long",
                year: "numeric",
            });

            // Jika tanggalnya sama, kembalikan hanya tanggal dan rentang waktu
            if (tanggalRawMulai === tanggalRawEnd) {
                return `${tanggalRawMulai}`;
            } else {
                const date = new Date(tanggalRawMulai);
                const finalSelesai = date?.toLocaleDateString("id-ID", {
                    day: "2-digit",
                    month: "long",
                    year: "numeric",
                });
                // Jika tanggalnya berbeda, kembalikan rentang tanggal dan waktu
                return `${tanggalRawMulai.split("-")[0]} - ${tanggalRawEnd}`;
            }
        },

        updateDateTime() {
            const now = new Date();
            this.currentDate = now.toLocaleDateString("id-ID", {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
            });
            this.currentMonthAndDate = now.toLocaleDateString("id-ID", {
                month: "long",
                year: "numeric",
            });
            this.currentTime = now.toLocaleTimeString("id-ID", {
                hour: "2-digit",
                minute: "2-digit",
                hour12: true,
            });
        },
        changeAttendancePeriod(period) {
            // In a real app, this would fetch data for the selected period
            console.log("Changed attendance period to:", period);
        },
        handleQuickAction(actionId) {
            // Handle different quick actions
            console.log("Quick action:", actionId);
            // In a real app, this would trigger the appropriate action/modal
        },
        viewAllHolidays() {
            console.log("View all holidays");
            // In a real app, this would navigate to the full holiday calendar
        },
        viewAllRequests() {
            console.log("View all leave requests");
            // In a real app, this would navigate to the leave requests page
        },
        newLeaveRequest() {
            console.log("New leave request");
            // In a real app, this would open a leave request form
        },
        async getAllLembur(url) {
            try {
                this.loadingLembur = true;

                const response = await axios
                    .get(url)
                    .then((response) => {
                        this.allDataLembur = response.data.data;
                        this.paginationLembur = response.data;
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            } catch (error) {
                console.log(error);
            } finally {
                this.loadingLembur = false;
            }
        },
    },
    mounted() {
        // console.log(this.isiBerita);
        this.convertAndSanitize(this.isiBerita);

        this.showModalBuletin = true;

        this.updateDateTime();
        this.getDateAbsen();
        this.getDataShift();
        this.getChartData();
        // this.getData();
        this.getAllLembur("/uDash/getAllLembur");
        this.getAllIzin("/uDash/getAllIzin");

        // Update time every minute
        this.timeInterval = setInterval(this.updateDateTime, 60000);
    },
    beforeUnmount() {
        clearInterval(this.timeInterval);
    },
};
</script>
<style>
/* Tooltip container adjustment */
.el-popper {
    border: none !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
    border-radius: 8px !important;
    padding: 2px !important;
    font-size: 1rem;
    font-weight: 600;
    color: var(--primary) !important;
    background: white !important;
}
[data-underline] {
    text-decoration: underline;
}
.el-popper__arrow {
    display: none !important;
}

.el-tooltip__popper.is-dark {
    background: transparent !important;
    border: none !important;
}

.el-popper {
    border: none !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
    border-radius: 8px !important;
    padding: 2px !important;
    font-size: 1rem;
    font-weight: 600;
    color: var(--primary) !important;
    background: white !important;
}

.el-popper__arrow {
    display: none !important;
}

.el-tooltip__popper.is-dark {
    background: transparent !important;
    border: none !important;
}

.el-popper.is-dark,
.el-popper.is-dark > .el-popper__arrow:before {
    border: none !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
    border-radius: 8px !important;
    padding: 2px !important;
    font-size: 1rem;
    font-weight: 600;
    color: var(--primary) !important;
    background: white !important;
}
.el-popper.is-dark,
.el-popper.is-dark > .el-popper__arrow:before {
    background: var(--surface);
}
</style>
<style scoped>
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
    --danger-light: #f8d7da; /* Merah muda, lebih terang untuk latar belakang atau efek disabled */
    --gray-light: #e9ecef;
    --el-slider-main-bg-color: #6366f1;
}

#el-id-9874-77 {
    background: var(--surface) !important;
}

.tanggalBar {
    -webkit-user-select: none; /* For Chrome, Safari, Opera */
    -moz-user-select: none; /* For Firefox */
    -ms-user-select: none; /* For Internet Explorer/Edge */
    user-select: none;
}

/* buletin */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1050;
}

.glass-modal {
    background: rgba(255, 0, 106, 0.15);
    border-radius: 16px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    width: 100%;
    max-width: 500px;
    animation: fadeIn 0.3s ease-out;
}

.glass-modal-content {
    padding: 20px;
    color: #fff;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    padding-bottom: 10px;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
}

.close {
    background: none;
    border: none;
    font-size: 1.5rem;
    padding: 0.1rem 0.7rem;
    border-radius: 12px;
    color: #fff;
    opacity: 0.8;
    cursor: pointer;
    transition: all 0.2s ease;
}

.close:hover {
    opacity: 1;
    /* padding: 0.2rem 1rem; */
    background: var(--danger);
}

.modal-body {
    margin-bottom: 20px;
}
.modal-body * {
    color: white !important;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    padding-top: 15px;
}

.btn {
    margin-left: 10px;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Tambahan untuk dark/light mode */
@media (max-width: 768px) {
    .glass-modal {
        /* background: rgba(255, 255, 255, 0.8); */
        color: #333;
        max-width: 95vw;
        max-height: 80vh;
    }
    .modal-header,
    .modal-footer {
        border-color: rgba(0, 0, 0, 0.1);
    }
    .close {
        color: #333;
    }
}
/* akhir buletin */
/* Tooltip container adjustment */
.el-popper {
    border: none !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
    border-radius: 8px !important;
    padding: 2px !important;
    font-size: 1rem;
    font-weight: 600;
    color: var(--primary) !important;
    background: white !important;
}

.el-popper__arrow {
    display: none !important;
}

.el-tooltip__popper.is-dark {
    background: transparent !important;
    border: none !important;
}

.skeleton-container {
    /* background-color: #f0f0f0; */
    overflow: hidden;
    position: relative;
}

.skeleton-icon,
.skeleton-text,
.skeleton-dropdown,
.skeleton-chart,
.skeleton-legend-dot,
.skeleton-legend-text {
    background-color: #e9ecef;
    position: relative;
    overflow: hidden;
}
.skeleton-text,
.skeleton-button,
.skeleton-circle {
    background-color: #e0e0e0;
    position: relative;
    overflow: hidden;
    border-radius: 4px;
}

.skeleton-text,
.skeleton-button,
.skeleton-circle {
    background-color: #e0e0e0;
    position: relative;
    overflow: hidden;
    border-radius: 4px;
}

/* Animation */
.skeleton-container::after,
.skeleton-text::after,
.skeleton-button::after,
.skeleton-circle::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.6),
        transparent
    );
    animation: shimmer 1.5s infinite;
}

.timeline-days {
    display: flex;
    gap: 1px;
    overflow-x: auto;
    padding-bottom: 8px;
}

.timeline-day {
    flex: 1;
    min-width: 120px;
    padding: 12px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.skeleton-day {
    background-color: #e9ecef !important;
}

.skeleton-icon,
.skeleton-text,
.skeleton-button,
.skeleton-badge {
    background-color: #e0e0e0;
    position: relative;
    overflow: hidden;
    border-radius: 4px;
}

/* Animation */
.skeleton-container::after,
.skeleton-icon::after,
.skeleton-text::after,
.skeleton-button::after,
.skeleton-badge::after,
.skeleton-day::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.6),
        transparent
    );
    animation: shimmer 1.5s infinite;
}

.info-card {
    background-color: #f5f5f5;
    border: none !important;
}

/* Animation */
.skeleton-container::after,
.skeleton-text::after,
.skeleton-button::after,
.skeleton-circle::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.6),
        transparent
    );
    animation: shimmer 1.5s infinite;
}

.info-card {
    background-color: #f5f5f5;
    border: none !important;
}

.skeleton-chart {
    background: conic-gradient(
        #e9ecef 0% 25%,
        #f0f2f5 25% 50%,
        #e9ecef 50% 75%,
        #f0f2f5 75% 100%
    );
}

.skeleton-text,
.skeleton-button,
.skeleton-date {
    background-color: #e0e0e0;
    position: relative;
    overflow: hidden;
}

/* Animation */
.skeleton-container::after,
.skeleton-text::after,
.skeleton-button::after,
.skeleton-date::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.6),
        transparent
    );
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

.badge-lembur {
    padding: 0.2rem 0.4rem;
    border-radius: 20rem;
    color: white;
    background: var(--warning) !important;
}
.badge-lembur-selesai {
    padding: 0.2rem 0.4rem;
    border-radius: 20rem;
    color: white;
    background: var(--success) !important;
}

.card {
    border-radius: 12px;
    overflow: hidden;
}

.bg-gradient-primary {
    background: var(--gradient-primary);
    opacity: 0.95;
}

.timeline-days {
    display: flex;
    padding: 0 10px;
    overflow-x: auto;
    scrollbar-width: none; /* Firefox */
}

.timeline-days::-webkit-scrollbar {
    display: none; /* Chrome/Safari */
}

.timeline-day {
    flex: 1;
    min-width: 80px;
    padding: 12px 8px;
    margin: 0 2px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid #e9ecef;
}

.timeline-day:hover {
    background-color: #f8f9fa;
    transform: translateY(-2px);
}

.timeline-day.active {
    background-color: #e7f1ff;
    border-color: #cfe2ff;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
}

.day-header {
    text-align: center;
    margin-bottom: 8px;
}

.day-name {
    font-weight: 600;
    font-size: 0.9rem;
}

.day-date {
    font-size: 0.75rem;
    color: #6c757d;
}

.day-shift {
    text-align: center;
}

.shift-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.shift-hours {
    font-size: 0.75rem;
    color: #495057;
}

.day-off {
    background-color: #f8f9fa;
}

.day-off-text {
    font-size: 0.75rem;
    color: #6c757d;
    font-style: italic;
}

.selected-day-details {
    border-top: 1px dashed #dee2e6;
    border-bottom: 1px dashed #dee2e6;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    font-size: 0.85rem;
}

.detail-label {
    color: #6c757d;
}

.detail-value {
    font-weight: 500;
}

.day-off-content {
    color: #6c757d;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .timeline-day {
        min-width: 70px;
        padding: 8px 4px;
    }

    .day-name {
        font-size: 0.8rem;
    }

    .shift-badge {
        font-size: 0.65rem;
    }
}

/* chart */
#chart-donut > div {
    display: flex;
    justify-content: center;
}
.legend-manual {
    margin-left: 20px;
    font-family: "Arial", sans-serif;
    font-size: 14px;
}
.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}
.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
}
.legend-text {
    color: #333;
}

/* Styling untuk teks di tengah donut, ini mungkin perlu disesuaikan */
#chart-donut .apexcharts-text tspan:first-child {
    font-size: 36px; /* Ukuran font untuk 86% */
    font-weight: 700;
    fill: #000;
}
#chart-donut .apexcharts-text tspan:last-child {
    font-size: 12px; /* Ukuran font untuk 'Persentase kehadiran' */
    font-weight: 400;
    fill: #555;
}
.col-md-6 {
    padding: 0 8px;
}

/* akhirchart */
/* calendar */
.wrapper-calendar {
    height: 10.2rem;
}
.wrapper-calendar-2 {
    height: 19em;
}
.cardFloat {
    width: 100%;
    padding-bottom: 3rem !important;
    z-index: 9;
    border-radius: 12px;

    background: var(--gradient-primary);
    padding: 0.7rem 0.7rem;
    opacity: 0.95;
}
.cardIn {
    background: var(--surface);
    border-radius: 12px;
    z-index: 15;
    width: 100%;
    top: 3rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--border);
}

.border-primary {
    border: 1px solid var(--border);
}

.dateCard {
    flex-grow: 1;
    padding: 0.5rem;
    margin-bottom: 4px;
    text-align: center;
    color: white;
    border-radius: 10px;
    background: var(--primary);
}

.dateCard.lost {
    background: var(--danger);
}
.dateCard.weekday {
    background: var(--secondary);
}
.dateCard.holiday {
    background: var(--warning);
}

/* akhir calendar */

/* info bar */
.leave-info-panel {
}

.info-card {
    transition: all 0.3s ease;
    border: 1px solid rgba(0, 0, 0, 0.05);
    border-radius: 12px !important;
}

.info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.icon-circle {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Color variants */
.bg-primary-light {
    background-color: rgba(13, 110, 253, 0.1);
}

.bg-warning-light {
    background-color: rgba(255, 193, 7, 0.1);
}

.bg-danger-light {
    background-color: rgba(220, 53, 69, 0.1);
}

/* Animation */
.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .icon-circle {
        width: 30px;
        height: 30px;
        margin-top: 8px;
        margin-right: 8px;
        font-size: 0.9rem;
        position: absolute;
        right: 0;
        top: 0;
    }
    .info-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: space-between;
    }

    .info-value {
        font-size: 1.25rem !important;
    }
}

/* akhir infobar */

/* button */

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

.btn-close {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-close:hover {
    background: var(--surface-soft);
    color: var(--text);
}

.card-body {
    overflow: auto;
    padding: 0.5rem 1rem;
}
/* Akhir button */
.card-wrap {
    background: var(--surface);
    border-radius: 20px;
    padding: 1rem 0.5rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--border);
}
.content-container {
    background: var(--gradient-primary);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--border);
    opacity: 0.95;
}
/* Base Styles */
.hris-dashboard {
    /* font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; */

    min-height: 100vh;
}

/* Header Styles */
.dashboard-header {
    background: var(--surface);
    padding: 15px 20px;
    border-radius: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    align-items: center;
    border: 1px solid var(--border);
    width: 100%;
    height: 100%;
}

.current-date {
    display: flex;
    flex-direction: column;
}

.date {
    font-size: 1rem;
    font-weight: 500;
}

.time {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
}

/* Stat Card Styles */
.quick-stats {
    margin: 0 -10px;
}

.stat-card {
    position: relative;
    padding: 20px;
    border-radius: 10px;
    color: white;
    height: 120px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.stat-unit {
    font-size: 0.8rem;
    opacity: 0.8;
}

.stat-icon {
    position: absolute;
    right: 45px;
    bottom: 45px;
    font-size: 2.5rem;
    opacity: 0.2;
}

.overtime {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.leave {
    background: linear-gradient(135deg, #2b5876 0%, #4e4376 100%);
}

.sick {
    background: linear-gradient(135deg, #f54ea2 0%, #ff7676 100%);
}

.permission {
    background: linear-gradient(135deg, #ff7eb3 0%, #ff758c 100%);
}

/* Card Styles */
.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);

    margin-bottom: 20px;
}

.card-header {
    background-color: white;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 15px 20px;
    border-radius: 12px 10px 0 0 !important;
}

/* Attendance Summary Styles */
.attendance-stats {
    display: flex;
    justify-content: space-between;
    text-align: center;
    margin-bottom: 20px;
}

.attendance-item {
    flex: 1;
}

.attendance-value {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.attendance-label {
    font-size: 0.8rem;
    color: #7f8c8d;
}

/* Quick Action Styles */
.btn-action {
    width: 100%;
    padding: 12px 5px;
    border-radius: 8px;
    font-size: 0.8rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
    background-color: white;
}

.btn-action i {
    font-size: 1.2rem;
    margin-bottom: 5px;
}

.btn-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-overtime {
    color: #667eea;
}

.btn-leave {
    color: #2b5876;
}

.btn-sick {
    color: #f54ea2;
}

.btn-permission {
    color: #ff7eb3;
}

.btn-late {
    color: #f39c12;
}

.btn-early {
    color: #e74c3c;
}

.btn-shift {
    color: #9b59b6;
}

.btn-attendance {
    color: #27ae60;
}

/* Holiday Card Styles */
.upcoming-holiday {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    text-align: center;
}

.holiday-date {
    font-size: 0.9rem;
    color: #7f8c8d;
}

.holiday-name {
    font-size: 1.2rem;
    font-weight: 600;
    margin: 5px 0;
    color: #2c3e50;
}

.holiday-days {
    font-size: 0.9rem;
    color: #e74c3c;
    font-weight: 500;
}

.holiday-item {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.holiday-item:last-child {
    border-bottom: none;
}

.holiday-item-date {
    width: 100px;
    font-size: 0.8rem;
    color: #7f8c8d;
}

.holiday-item-name {
    flex: 1;
    font-size: 0.9rem;
}

.type-sakit {
    background: var(--danger);
}
.type-terlambat {
    background: var(--warning);
}
.type-pulang-cepat {
    background: var(--info);
}
.type-tidak-masuk,
.type-cuti-tahunan,
.type-izin-pribadi {
    background: var(--primary);
}

/* Leave Request Styles */
.leave-request {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
    align-items: center;
}

.leave-request:last-child {
    border-bottom: none;
}

.request-type {
    font-size: 0.7rem;
    padding: 3px 8px;
    border-radius: 20px;
    color: white;
    margin-right: 10px;
    text-transform: uppercase;
    font-weight: 600;
}

.type-cuti {
    background-color: #3498db;
}

.type-sakit {
    background-color: #e74c3c;
}

.type-Iz {
    background-color: #f39c12;
}

.request-dates {
    flex: 1;
    font-size: 0.9rem;
}

.request-status {
    font-size: 0.8rem;
    font-weight: 500;
}

.status-Selesai {
    color: #27ae60;
}

.status-Diproses {
    color: #f39c12;
}

.status-Ditolak {
    color: #e74c3c;
}

/* Shift Card Styles */
.current-shift {
    display: flex;
    margin-bottom: 20px;
    align-items: center;
}

.shift-info {
    flex: 1;
}

.shift-name {
    font-size: 1.1rem;
    font-weight: 600;
}

.shift-time {
    font-size: 0.9rem;
    color: #7f8c8d;
}

.shift-progress {
    flex: 2;
    padding-left: 20px;
}

.shift-time-left {
    font-size: 0.8rem;
    text-align: right;
    color: #7f8c8d;
    margin-top: 5px;
}

.upcoming-shifts {
    margin-top: 25px;
}

.upcoming-shifts h6 {
    font-size: 0.9rem;
    color: #7f8c8d;
    margin-bottom: 15px;
    text-transform: uppercase;
}

.upcoming-shift {
    display: flex;
    margin-bottom: 12px;
}

.shift-day {
    width: 80px;
    font-size: 0.9rem;
    font-weight: 500;
}

.shift-details {
    flex: 1;
}

.shift-details span {
    display: block;
}

.shift-name {
    font-size: 0.9rem;
    font-weight: 500;
}

.shift-time {
    font-size: 0.8rem;
    color: #7f8c8d;
}

/* Responsive Styles */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        text-align: center;
    }
    .btn-action {
        /* max-width: 90px; */
        padding: 12px 0px !important;
    }
    .current-date {
        margin-top: 10px;
        align-items: center;
    }

    .attendance-stats {
        flex-wrap: wrap;
    }

    .attendance-item {
        flex: 50%;
        margin-bottom: 15px;
    }

    .current-shift {
        flex-direction: column;
        align-items: flex-start;
    }

    .shift-progress {
        width: 100%;
        padding-left: 0;
        margin-top: 15px;
    }

    .btn-action {
        padding: 8px 5px;
        font-size: 0.7rem;
    }

    .btn-action i {
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .stat-card {
        height: 100px;
        padding: 15px;
    }

    .stat-value {
        font-size: 1.5rem;
    }

    .stat-icon {
        font-size: 2rem;
    }

    .quick-actions-card .col-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}
</style>

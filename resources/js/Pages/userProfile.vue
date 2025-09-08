<template>
    <div class="profile-container">
        <!-- Header Section -->
        <header class="profile-header fade-in">
            <div class="header-content">
                <div class="avatar-section">
                    <div
                        class="avatar-container d-flex justify-content-center align-items-center"
                    >
                        <i
                            alt="User Avatar"
                            class="bi bi-person-circle d-flex justify-content-center align-items-center"
                        ></i>
                    </div>
                    <h1 class="employee-name mb-2">
                        {{ user.Nama ? user.Nama : "-" }}
                    </h1>
                    <p class="employee-id">
                        ID Absen:
                        {{ user.UserID_Absen ? user.UserID_Absen : "-" }} |
                        Divisi: {{ divisi ? divisi : "-" }}
                    </p>
                </div>

                <div class="approvers-section">
                    <h3 class="section-title">
                        <i
                            class="bi bi-people-fill d-flex justify-content-center align-items-center"
                        ></i
                        >Approver Izin
                    </h3>
                    <div class="approver-list">
                        <div
                            class="approver-item"
                            v-for="(approver, index) in nama_Approver"
                            :key="index"
                        >
                            <div class="approver-avatar">
                                <i
                                    class="bi bi-person d-flex justify-content-center align-items-center"
                                ></i>
                            </div>
                            <div class="approver-info">
                                <div class="approver-name">
                                    {{ approver.Nama }}
                                </div>
                                <div
                                    class="approver-position d-flex justify-content-start align-items-center"
                                >
                                    Approver {{ approver.order_flow }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <button
                        class="btn btn-reset text-white"
                        @click="resetPassword"
                    >
                        <i
                            class="bi bi-arrow-repeat d-flex justify-content-center align-items-center"
                        ></i>
                        Reset Password
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="profile-main">
            <div class="profile-grid">
                <!-- Personal Info Section -->
                <section class="profile-section personal-info fade-in">
                    <div class="section-header">
                        <h2>
                            <i
                                class="bi bi-info-circle d-flex justify-content-center align-items-center"
                            ></i>
                            Informasi Pribadi
                        </h2>
                        <button class="edit-btn" @click="editPersonalInfo">
                            <i
                                class="bi bi-gear d-flex justify-content-center align-items-center"
                            ></i>
                            Edit
                        </button>
                    </div>
                    <div class="section-content">
                        <div class="info-item">
                            <label class="fw-bold">Email</label>
                            <p>{{ user.Email ? user.Email : "-" }}</p>
                        </div>
                        <div class="info-item">
                            <label class="fw-bold">Nomor Telepon</label>
                            <p>+{{ user.HP ? user.HP : "-" }}</p>
                        </div>
                        <div class="info-item">
                            <label class="fw-bold">Alamat</label>
                            <p>{{ user.Alamat ? user.Alamat : "-" }}</p>
                        </div>
                        <div class="info-item">
                            <label class="fw-bold">Departemen</label>
                            <p>{{ divisi ? divisi : "-" }}</p>
                        </div>
                    </div>
                </section>

                <!-- Shift Schedule Section -->
                <!-- <section class="profile-section shift-schedule">
                    <div class="section-header">
                        <h2>
                            <i class="fas fa-calendar-alt"></i> Jadwal Shift
                        </h2>
                    </div>
                    <div class="section-content">
                        <div class="shift-calendar">
                            <div
                                class="shift-day"
                                v-for="(shift, index) in shiftSchedule"
                                :key="index"
                            >
                                <div class="day-date">{{ shift.date }}</div>
                                <div
                                    class="shift-type"
                                    :class="{
                                        'no-shift': shift.type === 'NO SHIFT',
                                    }"
                                >
                                    {{ shift.type }}
                                </div>
                            </div>
                        </div>
                        <div class="current-shift">
                            <h3>Shift Hari Ini</h3>
                            <div class="shift-details">
                                <p>
                                    <strong>Masuk:</strong> 08:30 (Han'mi SHIFT
                                    QG-K2)
                                </p>
                                <p>
                                    <strong>Pulang:</strong> 16:30 (Beeki Shift
                                    QG-K2)
                                </p>
                            </div>
                        </div>
                    </div>
                </section> -->

                <!-- Menyetujui Izin Section -->
                <section
                    v-if="nama_mengapprove?.length != 0"
                    class="profile-section overtime-history fade-in"
                >
                    <div class="section-header">
                        <h2>
                            <i
                                class="bi bi-check2-circle d-flex justify-content-center align-items-center"
                            ></i
                            >Menyetujui Izin
                        </h2>
                        <!-- <button class="view-all-btn" @click="viewAllOvertime">
                            Lihat Semua
                        </button> -->
                    </div>
                    <div class="section-content">
                        <!-- <div class="overtime-summary">
                            <p>
                                Total Lembur Bulan Ini:
                                <strong>{{ totalOvertime }} jam</strong>
                            </p>
                        </div> -->
                        <div class="overtime-list">
                            <div
                                class="overtime-item row"
                                v-for="(item, index) in nama_anggota"
                                :key="index"
                            >
                                <div class="overtime-date col-12">
                                    {{
                                        truncateText(capitalize(item.Nama), 15)
                                    }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Team Members Section -->
                <section
                    v-if="nama_anggota?.length != 0"
                    class="profile-section team-members fade-in"
                >
                    <div class="section-header">
                        <h2>
                            <i
                                class="bi bi-people d-flex justify-content-center align-items-center"
                            ></i>
                            Anggota Team
                        </h2>
                    </div>
                    <div class="section-content team">
                        <div class="team-grid">
                            <div
                                class="team-member"
                                v-for="(member, index) in nama_anggota"
                                :key="index"
                            >
                                <div class="member-info">
                                    <h4>{{ member.Nama }}</h4>
                                    <p>{{ member.divisi }}</p>
                                </div>
                                <div
                                    v-tooltip="
                                        'Jika salah atau kosong hubungi admin'
                                    "
                                    class="contact-btn"
                                >
                                    {{ member.HP }}
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Tergabung pada team Section -->
                <section class="profile-section leave-requests fade-in">
                    <div class="section-header">
                        <h2>
                            <i
                                class="bi bi-arrow-down-left-square d-flex justify-content-center align-items-center"
                            ></i
                            >Tergabung pada Team
                        </h2>
                    </div>
                    <div class="section-content d-flex flex-column gap-2">
                        <div
                            v-for="(team, idx) in nama_tergabung"
                            :key="idx"
                            class="request-item"
                        >
                            <div class="request-type text-black">
                                {{ team.Nama }}
                            </div>
                            <div class="request-date">{{ team.divisi }}</div>
                            <div class="request-status bg-secondary text-white">
                                {{ team.status }}
                                {{ team.HP ? team.HP : "-" }}
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Quick Actions Section -->
                <!-- <section class="profile-section quick-actions">
                    <div class="section-header">
                        <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                    </div>
                    <div class="section-content">
                        <div class="action-grid">
                            <button class="action-btn" @click="requestOvertime">
                                <i class="fas fa-clock"></i>
                                <span>Ajukan Lembur</span>
                            </button>
                            <button class="action-btn" @click="requestLeave">
                                <i class="fas fa-calendar-minus"></i>
                                <span>Ajukan Izin</span>
                            </button>
                            <button class="action-btn" @click="downloadPaySlip">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span>Slip Gaji</span>
                            </button>
                            <button class="action-btn" @click="openSettings">
                                <i class="fas fa-cog"></i>
                                <span>Pengaturan</span>
                            </button>
                        </div>
                    </div>
                </section> -->
            </div>
        </main>

        <!-- Avatar Editor Modal -->
        <!-- <div class="modal" v-if="showAvatarEditor">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Ubah Foto Profil</h3>
                    <button class="close-btn" @click="closeAvatarEditor">
                        &times;
                    </button>
                </div>
                <div class="modal-body">
                    <div class="avatar-preview">
                        <img :src="newAvatar" alt="Preview" />
                    </div>
                    <div class="avatar-options">
                        <button class="upload-btn" @click="triggerFileInput">
                            <i class="fas fa-upload"></i> Upload Foto
                        </button>
                        <input
                            type="file"
                            ref="fileInput"
                            @change="handleAvatarUpload"
                            accept="image/*"
                            style="display: none"
                        />
                        <button class="webcam-btn">
                            <i class="fas fa-camera"></i> Ambil Foto
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="cancel-btn" @click="closeAvatarEditor">
                        Batal
                    </button>
                    <button class="save-btn" @click="saveAvatar">Simpan</button>
                </div>
            </div>
        </div> -->

        <!-- Edit Personal Info Modal -->
    </div>

    <div class="modal-overlay" :class="{ show: showResetPasswordModal }">
        <div class="modal-content reset-password-modal">
            <div class="modal-header">
                <h3>
                    <i class="bi bi-key-fill me-2"></i>
                    Reset Password
                </h3>
                <button class="close-btn" @click="closeResetPasswordModal">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="submitResetPassword">
                    <div class="form-group">
                        <label for="currentPassword">Password Saat Ini</label>
                        <div class="password-input-container">
                            <input
                                :type="
                                    showCurrentPassword ? 'text' : 'password'
                                "
                                id="currentPassword"
                                v-model="currentPassword"
                                placeholder="Masukkan password saat ini"
                                required
                            />
                            <button
                                type="button"
                                class="password-toggle"
                                @click="
                                    showCurrentPassword = !showCurrentPassword
                                "
                            >
                                <i
                                    :class="
                                        showCurrentPassword
                                            ? 'bi bi-eye-slash'
                                            : 'bi bi-eye'
                                    "
                                ></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="newPassword">Password Baru</label>
                        <div class="password-input-container">
                            <input
                                :type="showNewPassword ? 'text' : 'password'"
                                id="newPassword"
                                v-model="newPassword"
                                placeholder="Masukkan password baru"
                                required
                                @input="checkPasswordStrength"
                            />
                            <button
                                type="button"
                                class="password-toggle"
                                @click="showNewPassword = !showNewPassword"
                            >
                                <i
                                    :class="
                                        showNewPassword
                                            ? 'bi bi-eye-slash'
                                            : 'bi bi-eye'
                                    "
                                ></i>
                            </button>
                        </div>
                        <div class="password-strength-meter">
                            <div
                                class="strength-bar"
                                :class="strengthClass"
                            ></div>
                        </div>
                        <div class="password-requirements">
                            <p>Password harus mengandung:</p>
                            <ul>
                                <li
                                    :class="{ 'requirement-met': hasMinLength }"
                                >
                                    Minimal 8 karakter
                                </li>
                                <li
                                    :class="{ 'requirement-met': hasUpperCase }"
                                >
                                    Huruf besar (A-Z)
                                </li>
                                <li
                                    :class="{ 'requirement-met': hasLowerCase }"
                                >
                                    Huruf kecil (a-z)
                                </li>
                                <li :class="{ 'requirement-met': hasNumber }">
                                    Angka (0-9)
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword"
                            >Konfirmasi Password Baru</label
                        >
                        <div class="password-input-container">
                            <input
                                :type="
                                    showConfirmPassword ? 'text' : 'password'
                                "
                                id="confirmPassword"
                                v-model="confirmPassword"
                                placeholder="Konfirmasi password baru"
                                required
                            />
                            <button
                                type="button"
                                class="password-toggle"
                                @click="
                                    showConfirmPassword = !showConfirmPassword
                                "
                            >
                                <i
                                    :class="
                                        showConfirmPassword
                                            ? 'bi bi-eye-slash'
                                            : 'bi bi-eye'
                                    "
                                ></i>
                            </button>
                        </div>
                        <p
                            class="validation-message"
                            v-if="confirmPassword && !passwordsMatch"
                        >
                            Password tidak cocok
                        </p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button
                    class="cancel-btn btn-secondary btn-modern"
                    @click="closeResetPasswordModal"
                >
                    Batal
                </button>
                <button
                    class="save-btn btn-modern btn-primary"
                    @click="submitResetPassword"
                    :disabled="!isFormValid || loading"
                    :class="{ 'btn-disabled': !isFormValid }"
                >
                    Simpan Perubahan
                    <i
                        v-if="loading"
                        class="spinner-border spinner-border-sm ms-2"
                    ></i>
                    <i
                        v-if="!loading"
                        class="bi bi-check-circle-fill ms-2 d-flex justify-content-center align-items-center"
                    ></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { ElMessage, ElNotification } from "element-plus";
import "element-plus/dist/index.css";
export default {
    name: "UserProfile",
    components: {
        ElMessage,
        ElNotification,
    },
    props: {
        user: Object,
        divisi: String,
        nama_Approver: Array,
        nama_anggota: Array,
        nama_mengapprove: Array,
        nama_tergabung: Array,
    },
    data() {
        return {
            loading: false,
            showResetPasswordModal: false,
            currentPassword: "",
            newPassword: "",
            confirmPassword: "",
            showCurrentPassword: false,
            showNewPassword: false,
            showConfirmPassword: false,
            passwordStrength: 0,

            showAvatarEditor: false,
            showEditPersonalInfo: false,
            newAvatar: "https://via.placeholder.com/150",
        };
    },
    computed: {
        totalOvertime() {
            return this.overtimeHistory
                .reduce((total, item) => total + item.hours, 0)
                .toFixed(2);
        },
        hasMinLength() {
            return this.newPassword.length >= 8;
        },
        hasUpperCase() {
            return /[A-Z]/.test(this.newPassword);
        },
        hasLowerCase() {
            return /[a-z]/.test(this.newPassword);
        },
        hasNumber() {
            return /[0-9]/.test(this.newPassword);
        },

        passwordsMatch() {
            return this.newPassword === this.confirmPassword;
        },
        isFormValid() {
            return (
                this.currentPassword &&
                this.hasMinLength &&
                this.hasUpperCase &&
                this.hasLowerCase &&
                this.hasNumber &&
                this.passwordsMatch
            );
        },
        strengthClass() {
            if (this.passwordStrength < 2) return "strength-weak";
            if (this.passwordStrength < 4) return "strength-medium";
            return "strength-strong";
        },
    },
    methods: {
        // Method untuk reset password
        resetPassword() {
            this.showResetPasswordModal = true;
        },
        closeResetPasswordModal() {
            this.showResetPasswordModal = false;
            this.resetForm();
        },
        resetForm() {
            this.currentPassword = "";
            this.newPassword = "";
            this.confirmPassword = "";
            this.showCurrentPassword = false;
            this.showNewPassword = false;
            this.showConfirmPassword = false;
            this.passwordStrength = 0;
        },
        checkPasswordStrength() {
            let strength = 0;

            if (this.newPassword.length >= 8) strength += 1;
            if (/[A-Z]/.test(this.newPassword)) strength += 1;
            if (/[a-z]/.test(this.newPassword)) strength += 1;
            if (/[0-9]/.test(this.newPassword)) strength += 1;
            if (/[!@#$%^&*(),.?":{}|<>]/.test(this.newPassword)) strength += 1;

            this.passwordStrength = strength;
        },
        async submitResetPassword() {
            if (!this.isFormValid) return;
            try {
                this.loading = true;
                const response = await axios.post("/updatePassword", {
                    oldPass: this.currentPassword,
                    newPass: this.newPassword,
                });
                if (response.status === 200) {
                    ElNotification.success("Password berhasil diubah!");
                } else {
                    ElMessage.error(
                        "Gagal mengubah password. Silakan coba lagi."
                    );
                }
            } catch (error) {
                console.error("Error updating password:", error);
                ElMessage.error("Gagal mengubah password. Silakan coba lagi.");
            } finally {
                this.loading = false;
                this.closeResetPasswordModal();
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
        truncateText(text, length) {
            return text.length > length
                ? text.substring(0, length) + "..."
                : text;
        },
        openAvatarEditor() {
            this.showAvatarEditor = true;
        },
        closeAvatarEditor() {
            this.showAvatarEditor = false;
        },
        triggerFileInput() {
            this.$refs.fileInput.click();
        },
        handleAvatarUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.newAvatar = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        saveAvatar() {
            // In a real app, you would upload the avatar to your server here
            this.closeAvatarEditor();
        },
        editPersonalInfo() {
            this.showEditPersonalInfo = true;
        },
        viewAllOvertime() {
            // Navigate to full overtime history page
        },
        newLeaveRequest() {
            // Open leave request form
        },
        contactMember(member) {
            // Open contact dialog for the member
        },
        requestOvertime() {
            // Open overtime request form
        },
        requestLeave() {
            // Open leave request form
        },
        downloadPaySlip() {
            // Download payslip
        },
        openSettings() {
            // Open settings page
        },
    },
};
</script>

<style scoped>
/* Base Styles */
*,
*::before,
*::after {
    --primary-color: #6366f1;
    --secondary-color: #64748b;
    --accent-color: #e74c3c;
    --light-color: #ecf0f1;
    --dark-color: #1e293b;
    --success-color: #10b981;
    --warning-color: #f59e0b;
    --danger-color: #ef4444;
    --border-radius: 20px;
    --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

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
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: #f5f7fa;
}

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

/* General Buttons */
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
.action-menu {
    position: absolute !important; /* Popup akan mengikuti posisi elemen yang diklik */
    z-index: 1000; /* Pastikan popup tetap di atas elemen lainnya */
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 4px;
    padding: 10px;
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
.section-content {
    max-height: 15rem;
    overflow: auto;
}
.profile-header {
    background: linear-gradient(
        135deg,
        var(--primary-color),
        var(--secondary-color)
    );
    color: white;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    margin-bottom: 30px;
    position: relative;
}

.header-content {
    padding: 0px;
    position: relative;
}

.avatar-section {
    text-align: center;
}

.avatar-container {
    width: 120px;
    height: 120px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.2);
    border: 4px solid white;
    overflow: hidden;
}

.avatar-container i {
    font-size: 5rem;
    color: white;
}

.employee-name {
    font-weight: 700;
    font-size: 1.8rem;
}

.employee-id {
    font-size: 1rem;
    opacity: 0.9;
}

.approvers-section {
    background-color: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    padding: 20px;
    margin: 10px 0;
    backdrop-filter: blur(5px);
}

.section-title {
    font-size: 1.2rem;
    margin-bottom: 15px;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.section-title i {
    margin-right: 10px;
}

.approver-list {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.approver-item {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 10px 15px;
    display: flex;
    align-items: center;
    min-width: 200px;
    transition: all 0.3s ease;
}

.approver-item:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-3px);
}

.approver-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    color: var(--primary-color);
}

.approver-info {
    flex-grow: 1;
}

.approver-name {
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 2px;
}

.approver-position {
    font-size: 0.8rem;
    opacity: 0.9;
}

.action-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-top: 20px;
}

.btn-reset {
    background-color: var(--accent-color);
    border: none;
    border-radius: 50px;
    padding: 10px 25px;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-reset:hover {
    background-color: #c0392b;
    transform: translateY(-3px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.btn-reset i {
    margin-right: 8px;
}

@media (max-width: 768px) {
    .approvers-section {
        width: 100%;
        text-align: start;
    }
    .header-content {
        padding: 0px;
    }

    .approver-list {
        flex-direction: column;
    }

    .approver-item {
        width: 100%;
    }

    .action-buttons {
        flex-direction: column;
    }
}
.profile-container {
    /* max-width: 1200px; */
    /* margin: 0 auto; */
    padding: 0px;
}

/* Header Styles */
.profile-header {
    background: var(--gradient-primary);
    color: white;
    border-radius: var(--border-radius);
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: var(--box-shadow);
}

.header-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0px;
}

.avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.avatar-container {
    position: relative;
    width: 120px;
    height: 120px;
    /* margin-bottom: 15px; */
}

.avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid white;
}

.edit-avatar-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background-color: var(--secondary-color);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.edit-avatar-btn:hover {
    background-color: darken(var(--secondary-color), 10%);
    transform: scale(1.1);
}

.employee-id {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 5px 10px;

    border-radius: 20px;
    font-size: 0.9rem;
}

.stats-section {
    display: flex;
    gap: 15px;
    width: 100%;
    justify-content: center;
}

.stat-card {
    background-color: rgba(255, 255, 255, 0.1);
    padding: 15px;
    border-radius: var(--border-radius);
    text-align: center;
    flex: 1;
    max-width: 150px;
}

.stat-card h3 {
    font-size: 0.9rem;
    margin-bottom: 5px;
    font-weight: normal;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
}

/* Main Content Styles */
.profile-main {
    margin-bottom: 40px;
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.profile-section {
    background-color: white;
    border-radius: var(--border-radius);
    padding: 20px;
    box-shadow: var(--box-shadow);
}

.profille-section.team-members {
    max-height: 10rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.section-header h2 {
    font-size: 1.2rem;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.edit-btn,
.view-all-btn,
.new-request-btn {
    background-color: var(--secondary-color);
    color: white;
    border: none;
    padding: 5px 10px;
    border-radius: var(--border-radius);
    font-size: 0.8rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: background-color 0.3s;
}

.edit-btn:hover,
.view-all-btn:hover,
.new-request-btn:hover {
    background-color: darken(var(--secondary-color), 10%);
}

/* Shift Schedule Styles */
.shift-calendar {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 15px;
}

.shift-day {
    text-align: center;
    padding: 8px;
    border-radius: var(--border-radius);
    background-color: #f8f9fa;
}

.day-date {
    font-weight: bold;
    margin-bottom: 5px;
}

.shift-type {
    font-size: 0.9rem;
    color: var(--secondary-color);
    font-weight: bold;
}

.no-shift {
    color: var(--warning-color);
}

.current-shift {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: var(--border-radius);
}

.current-shift h3 {
    margin-bottom: 10px;
    font-size: 1rem;
}

.shift-details p {
    margin-bottom: 5px;
    font-size: 0.9rem;
}

/* Overtime History Styles */
.overtime-summary {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: var(--border-radius);
    margin-bottom: 15px;
    text-align: center;
}

.overtime-list {
    max-height: 200px;
    overflow-y: auto;
}

.overtime-item {
    /* display: flex; */
    /* justify-content: space-between; */
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.overtime-date {
    color: var(--dark-color);
}

.overtime-hours {
    font-weight: bold;
    color: var(--secondary-color);
}

/* Leave Requests Styles */
.request-item {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: var(--border-radius);
}

.request-type {
    font-weight: bold;
    color: var(--accent-color);
    margin-bottom: 5px;
}

.request-date {
    font-size: 0.9rem;
    color: var(--dark-color);
    margin-bottom: 5px;
}

.request-status {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 0.8rem;
}

.completed {
    background-color: #d4edda;
    color: #155724;
}

/* Team Members Styles */
.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    max-height: 15rem;
    overflow-y: auto;
}

.team-member {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 10px;
    border-radius: var(--border-radius);
    background-color: #f8f9fa;
    transition: transform 0.3s;
}

.team-member:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.member-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    margin-bottom: 10px;
}

.member-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.member-info h4 {
    font-size: 0.9rem;
    margin-bottom: 3px;
}

.member-info p {
    font-size: 0.8rem;
    color: #666;
}

.contact-btn {
    margin-top: 5px;
    border-radius: var(--border-radius);
    background-color: var(--secondary-color);
    color: white;
    padding: 5px 10px;
    font-size: 0.8rem;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s;
}

.contact-btn:hover {
    background-color: darken(var(--secondary-color), 10%);
    color: black;
}

/* Quick Actions Styles */
.action-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 15px 10px;
    background-color: #f8f9fa;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all 0.3s;
}

.action-btn:hover {
    background-color: var(--secondary-color);
    color: white;
    transform: translateY(-3px);
}

.action-btn i {
    font-size: 1.5rem;
    margin-bottom: 5px;
}

.action-btn span {
    font-size: 0.8rem;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background-color: white;
    border: none;
    border-radius: var(--border-radius);
    width: 90%;
    max-width: 500px;
    overflow: hidden;
}

.modal-header {
    padding: 15px 20px;
    background: var(--gradient-primary);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
}

.modal-body {
    padding: 20px;
}

.avatar-preview {
    width: 150px;
    height: 150px;
    margin: 0 auto 20px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #eee;
}

.avatar-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-options {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.upload-btn,
.webcam-btn {
    padding: 8px 15px;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9rem;
}

.upload-btn {
    background-color: var(--secondary-color);
    color: white;
}

.webcam-btn {
    background-color: #f8f9fa;
    color: var(--dark-color);
}

.modal-footer {
    padding: 15px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid #eee;
}

.cancel-btn,
.save-btn {
    padding: 8px 15px;
    border: none;
    border-radius: var(--border-radius);
    cursor: pointer;
}

.cancel-btn {
    background-color: #f8f9fa;
    color: var(--dark-color);
}

.save-btn {
    background-color: var(--success-color);
    color: white;
}
.reset-password-modal {
    max-width: 500px;
}

.password-input-container {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input-container input {
    padding-right: 40px;
    width: 100%;
}

.password-toggle {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    color: var(--secondary-color);
    cursor: pointer;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--dark-color);
}

.form-group input {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.password-strength-meter {
    height: 5px;
    background-color: var(--gray-light);
    border-radius: 3px;
    margin-top: 8px;
    overflow: hidden;
}

.strength-bar {
    height: 100%;
    width: 0%;
    transition: width 0.3s, background-color 0.3s;
}

.strength-weak {
    width: 33.33%;
    background-color: var(--danger-color);
}

.strength-medium {
    width: 66.66%;
    background-color: var(--warning-color);
}

.strength-strong {
    width: 100%;
    background-color: var(--success-color);
}

.password-requirements {
    margin-top: 10px;
    font-size: 0.85rem;
    color: var(--text-muted);
}

.password-requirements p {
    margin-bottom: 5px;
    font-weight: 600;
}

.password-requirements ul {
    list-style: none;
    padding-left: 0;
    margin: 0;
}

.password-requirements li {
    margin-bottom: 3px;
    position: relative;
    padding-left: 20px;
}

.password-requirements li:before {
    content: "✗";
    position: absolute;
    left: 0;
    color: var(--danger-color);
}

.password-requirements li.requirement-met:before {
    content: "✓";
    color: var(--success-color);
}

.validation-message {
    color: var(--danger-color);
    font-size: 0.85rem;
    margin-top: 5px;
}

.btn-disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive styles */
@media (max-width: 576px) {
    .reset-password-modal {
        margin: 20px;
        width: calc(100% - 40px);
    }

    .modal-footer {
        flex-direction: column;
        gap: 10px;
    }

    .cancel-btn,
    .save-btn {
        width: 100%;
    }
}

/* Responsive Styles */
@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
    }

    .stats-section {
        flex-wrap: wrap;
    }

    .stat-card {
        min-width: 120px;
    }

    .profile-grid {
        grid-template-columns: 1fr;
    }

    .shift-calendar {
        grid-template-columns: repeat(2, 1fr);
    }

    .team-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .action-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .profile-header {
        padding: 15px;
    }

    .avatar-container {
        width: 100px;
        height: 100px;
    }

    .stat-card {
        min-width: 100px;
        padding: 10px;
    }

    .shift-calendar {
        grid-template-columns: 1fr;
    }

    .team-grid {
        grid-template-columns: 1fr;
    }
}
</style>

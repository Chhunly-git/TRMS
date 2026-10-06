<template>
  <div class="content-wrapper attendance-page" style="min-height: 1000px; background-color: #f4f6f9;">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark font-weight-bold page-title">គ្រប់គ្រងវត្តមានមន្ត្រី</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="card shadow-sm border-0 rounded-lg mb-4">
          <div class="card-body p-3">
            <div class="row align-items-center">
              <div class="col-md-3 mb-2 mb-md-0">
                <input type="date" class="form-control" v-model="selectedDate" @change="fetchAttendances" />
              </div>
              <div class="col-md-4 mb-2 mb-md-0">
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-right-0">
                      <i class="fas fa-search text-muted"></i>
                    </span>
                  </div>
                  <input type="text" class="form-control border-left-0" v-model="searchQuery" placeholder="ស្វែងរកឈ្មោះមន្ត្រី..." />
                </div>
              </div>
              <div class="col-md-5 text-md-right d-flex justify-content-md-end align-items-center flex-wrap">
                <button @click="openHikvisionModal" class="btn btn-dark px-3 shadow-sm mr-2 mb-1" title="គ្រប់គ្រងម៉ាស៊ីនស្កេនវត្តមាន HIKVISION">
                  <i class="fas fa-fingerprint text-warning mr-1"></i> ម៉ាស៊ីន HIKVISION
                  <span v-if="devices.length > 0" class="badge badge-success ml-1">{{ devices.length }}</span>
                </button>
                <button @click="triggerQuickDeviceSync" :disabled="syncingDevice" class="btn btn-outline-info px-3 shadow-sm mr-2 mb-1" title="ទាញយកវត្តមានពីម៉ាស៊ីន Hikvision សម្រាប់ថ្ងៃនេះ">
                  <i class="fas fa-satellite-dish mr-1" :class="{ 'fa-spin': syncingDevice }"></i> {{ syncingDevice ? 'កំពុង Sync...' : 'Sync ពីម៉ាស៊ីន' }}
                </button>
                <button @click="openImportModal" class="btn btn-success px-3 shadow-sm mr-2 mb-1">
                  <i class="fas fa-file-excel mr-1"></i> Import Excel
                </button>
                <button @click="fetchAttendances" class="btn btn-primary px-3 shadow-sm mb-1">
                  <i class="fas fa-sync-alt mr-1"></i> ផ្ទុកទិន្នន័យ
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm border-0 rounded-lg">
          <div class="card-body table-responsive p-0">
            <table class="table table-hover align-middle mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="text-center">ល.រ</th>
                  <th>ឈ្មោះ</th>
                  <th>តួនាទី</th>
                  <th class="text-center">អវត្តមាន</th>
                  <th class="text-center">ច្បាប់</th>
                  <th class="text-center">បេសកកម្ម</th>
                  <th>ម៉ោងវត្តមាន</th>
                  <th class="text-center" style="width: 140px;">ម៉ោងធ្វើការ</th>
                  <th class="text-center">សកម្មភាព</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="filteredAttendances.length === 0">
                  <td colspan="9" class="text-center py-4 text-muted">
                    មិនមានទិន្នន័យមន្ត្រីដែលត្រូវនឹងការស្វែងរកឡើយ
                  </td>
                </tr>
                <tr v-else v-for="(item, index) in filteredAttendances" :key="item.user_id">
                  <td class="text-center font-weight-bold">{{ index + 1 }}</td>
                  <td><div class="font-weight-bold text-dark">{{ item.user?.name_kh || item.user?.name }}</div></td>
                  <td><span class="text-secondary">{{ item.user?.position?.title_kh || '---' }}</span></td>
                  
                  <td class="text-center">
                    <input type="checkbox" :checked="item.status === 'ABSENT'" @change="handleSpecialStatus(item, 'ABSENT')" class="custom-checkbox checkbox-danger">
                  </td>
                  <td class="text-center">
                    <input type="checkbox" :checked="item.status === 'PERMISSION'" @change="handleSpecialStatus(item, 'PERMISSION')" class="custom-checkbox checkbox-info">
                  </td>
                  <td class="text-center">
                    <input type="checkbox" :checked="item.status === 'MISSION'" @change="handleSpecialStatus(item, 'MISSION')" class="custom-checkbox checkbox-primary">
                  </td>

                  <td>
                    <span v-if="item.status === 'ABSENT'" class="badge badge-danger px-2 py-1">អវត្តមាន: {{ item.note }}</span>
                    <span v-else-if="item.status === 'PERMISSION'" class="badge badge-info px-2 py-1">មានច្បាប់: {{ item.note }}</span>
                    <span v-else-if="item.status === 'MISSION'" class="badge badge-primary px-2 py-1">បេសកកម្ម: {{ item.note }}</span>
                    <span v-else>
                      <span class="badge px-2 py-1 mr-1" :class="item.status === 'LATE' ? 'badge-warning' : 'badge-success'">
                        {{ formatStatusKhmer(item.status) }}
                      </span> 
                      <span class="font-weight-bold">{{ item.check_in_time || '08:00' }}</span>
                      <span v-if="item.check_out_time" class="text-secondary small"> - {{ item.check_out_time }}</span>
                      <small v-if="item.note" class="text-info d-block"><i class="fas fa-comment-dots mr-1"></i>{{ item.note }}</small>
                    </span>
                  </td>

                  <td class="text-center">
                    <span v-if="getWorkingHoursDisplay(item)" class="badge badge-light border text-dark font-weight-bold px-2 py-1">
                      <i class="fas fa-stopwatch text-success mr-1"></i> {{ getWorkingHoursDisplay(item) }}
                    </span>
                    <span v-else-if="item.check_in_time && !item.check_out_time && ['PRESENT', 'LATE'].includes(item.status)" class="badge badge-light text-muted px-2 py-1">
                      <i class="fas fa-hourglass-half text-warning mr-1"></i> កំពុងធ្វើការ
                    </span>
                    <span v-else class="text-muted">---</span>
                  </td>

                  <td class="text-center">
                    <button @click="openModal(item)" class="btn btn-sm btn-outline-primary px-3 rounded-pill">
                      <i class="fas fa-clock mr-1"></i> កត់ត្រាម៉ោង
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" ref="attendanceModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <form @submit.prevent="saveAttendance" class="w-100">
          <div class="modal-content border-0 shadow-lg rounded-lg">
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>កត់ត្រាវត្តមានមន្ត្រី</h5>
              <button type="button" class="close text-white" @click="closeModal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
              <div class="row" v-if="['PRESENT', 'LATE'].includes(form.status)">
                <div class="col-6">
                  <label class="font-weight-bold text-secondary">ម៉ោងចូល:</label>
                  <input type="time" class="form-control" v-model="form.check_in_time" required>
                </div>
                <div class="col-6">
                  <label class="font-weight-bold text-secondary">ម៉ោងចេញ:</label>
                  <input type="time" class="form-control" v-model="form.check_out_time">
                </div>
                <div class="col-12 mt-2" v-if="modalCalculatedWorkingHours">
                  <div class="alert alert-success py-1 px-3 mb-0 small d-flex align-items-center">
                    <i class="fas fa-stopwatch mr-2"></i>
                    <span>ម៉ោងធ្វើការសរុប៖ <strong>{{ modalCalculatedWorkingHours }}</strong></span>
                  </div>
                </div>
              </div>

              <div class="form-group mt-3">
                <label class="font-weight-bold text-secondary">
                  {{ ['ABSENT', 'PERMISSION', 'MISSION'].includes(form.status) ? 'មូលហេតុ (ចាំបាច់):' : 'បញ្ជាក់បន្ថែម (មិនบังคับ):' }}
                </label>
                <textarea class="form-control" v-model="form.note" rows="3" :required="['ABSENT', 'PERMISSION', 'MISSION'].includes(form.status)" placeholder="សរសេរមូលហេតុ..."></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary px-4">រក្សាទុក</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Import Excel Modal -->
    <div v-if="showImportModal" class="custom-modal-backdrop" @click.self="closeImportModal">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable my-auto" role="document" style="width: 100%; max-width: 1050px;">
        <div class="modal-content border-0 shadow-lg rounded-lg font-khmer">
          <div class="modal-header bg-success text-white py-3">
            <h5 class="modal-title font-weight-bold m-0">
              <i class="fas fa-file-excel mr-2"></i> Import វត្តមានមន្ត្រីពី File Excel
            </h5>
            <button type="button" class="close text-white" @click="closeImportModal">
              <span>&times;</span>
            </button>
          </div>

          <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
            <!-- Step Instructions & Template Download -->
            <div class="alert alert-light border shadow-xs mb-4">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <h6 class="font-weight-bold text-dark mb-1">
                    <i class="fas fa-info-circle text-primary mr-1"></i> សេចក្តីណែនាំអំពីការ Import វត្តមាន
                  </h6>
                  <p class="text-muted small mb-0">
                    សូមទាញយកគំរូទម្រង់ Excel (.xlsx) ដែលមានរាយនាមមន្ត្រីរួចជាស្រេច រួចបំពេញព័ត៌មានស្ថានភាព ឬម៉ោងចូល/ចេញ និង Upload ចូលមកវិញ។
                  </p>
                </div>
                <div class="col-md-4 text-md-right mt-2 mt-md-0">
                  <button @click="downloadExcelTemplate" class="btn btn-outline-success btn-sm shadow-xs font-weight-bold">
                    <i class="fas fa-download mr-1"></i> ទាញយកគំរូ Excel (.xlsx)
                  </button>
                </div>
              </div>
            </div>

            <!-- Upload Box -->
            <div class="card border-dashed p-4 text-center bg-light rounded-lg mb-4 cursor-pointer" @click="triggerFileInput">
              <input
                type="file"
                ref="fileInputRef"
                class="d-none"
                accept=".xlsx,.xls,.csv"
                @change="handleFileSelected"
              />
              <div v-if="!importFileName">
                <i class="fas fa-file-excel fa-3x text-success mb-2"></i>
                <h6 class="font-weight-bold text-dark mb-1">ចុចទីនេះដើម្បីជ្រើសរើស File Excel (.xlsx, .xls, .csv)</h6>
                <small class="text-muted">គាំទ្រឯកសារ Excel និង CSV ដែលបានបំពេញទិន្នន័យវត្តមាន</small>
              </div>
              <div v-else class="d-flex align-items-center justify-content-center">
                <i class="fas fa-file-excel fa-2x text-success mr-2"></i>
                <span class="font-weight-bold text-dark mr-3">{{ importFileName }}</span>
                <button type="button" class="btn btn-xs btn-outline-secondary" @click.stop="resetFileSelection">
                  <i class="fas fa-times mr-1"></i> ប្តូរ File ផ្សេង
                </button>
              </div>
            </div>

            <!-- Loading during file parsing -->
            <div v-if="importingFile" class="text-center py-4 text-muted">
              <div class="spinner-border spinner-border-sm text-success mr-2" role="status"></div>
              កំពុងអានទិន្នន័យពី File Excel...
            </div>

            <!-- Preview Results Section -->
            <div v-if="parsedRecords.length > 0">
              <!-- Summary Counters -->
              <div class="row mb-3">
                <div class="col-sm-4 mb-2">
                  <div class="p-2 border rounded bg-white text-center">
                    <span class="text-muted small d-block">ទិន្នន័យសរុបក្នុង File</span>
                    <strong class="h5 text-dark m-0">{{ parsedRecords.length }} នាក់</strong>
                  </div>
                </div>
                <div class="col-sm-4 mb-2">
                  <div class="p-2 border rounded bg-white text-center">
                    <span class="text-muted small d-block">ត្រឹមត្រូវអាច Import បាន</span>
                    <strong class="h5 text-success m-0">{{ validRecordsCount }} នាក់</strong>
                  </div>
                </div>
                <div class="col-sm-4 mb-2">
                  <div class="p-2 border rounded bg-white text-center">
                    <span class="text-muted small d-block">មិនត្រូវគ្នា / មានបញ្ហា</span>
                    <strong class="h5 text-danger m-0">{{ invalidRecordsCount }} នាក់</strong>
                  </div>
                </div>
              </div>

              <!-- Preview Table -->
              <div class="table-responsive border rounded bg-white" style="max-height: 320px;">
                <table class="table table-sm table-hover table-striped mb-0 text-sm align-middle">
                  <thead class="bg-light sticky-top">
                    <tr>
                      <th class="text-center" style="width: 40px;">#</th>
                      <th>កូដមន្ត្រី</th>
                      <th>ឈ្មោះមន្ត្រី</th>
                      <th>កាលបរិច្ឆេទ</th>
                      <th class="text-center">ស្ថានភាព</th>
                      <th class="text-center">ម៉ោងចូល - ចេញ</th>
                      <th>មូលហេតុ / សម្គាល់</th>
                      <th class="text-center">ផ្ទៀងផ្ទាត់</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="rec in parsedRecords" :key="rec.row_index" :class="{ 'table-danger': !rec.isValid }">
                      <td class="text-center text-muted font-weight-bold">{{ rec.row_index }}</td>
                      <td>
                        <span class="font-weight-500">{{ rec.employee_code || '---' }}</span>
                      </td>
                      <td>
                        <strong :class="rec.isValid ? 'text-dark' : 'text-danger'">{{ rec.name || '---' }}</strong>
                      </td>
                      <td>{{ rec.date }}</td>
                      <td class="text-center">
                        <span class="badge px-2 py-1" :class="getStatusBadgeClass(rec.status)">
                          {{ formatStatusKhmer(rec.status) }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span v-if="['PRESENT', 'LATE'].includes(rec.status)">
                          {{ rec.check_in_time || '08:00' }} - {{ rec.check_out_time || '17:00' }}
                        </span>
                        <span v-else class="text-muted">---</span>
                      </td>
                      <td>
                        <small class="text-muted text-truncate d-block" style="max-width: 200px;" :title="rec.note">
                          {{ rec.note || '---' }}
                        </small>
                      </td>
                      <td class="text-center">
                        <span v-if="rec.isValid" class="badge badge-success px-2 py-1">
                          <i class="fas fa-check mr-1"></i> ត្រឹមត្រូវ
                        </span>
                        <span v-else class="badge badge-danger px-2 py-1" :title="rec.errorMessage">
                          <i class="fas fa-exclamation-triangle mr-1"></i> {{ rec.errorMessage }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light justify-content-between">
            <button type="button" class="btn btn-secondary px-3" @click="closeImportModal">
              <i class="fas fa-times mr-1"></i> បោះបង់
            </button>
            <button
              type="button"
              class="btn btn-success px-4 font-weight-bold shadow-sm"
              :disabled="validRecordsCount === 0 || isSubmittingImport"
              @click="confirmImport"
            >
              <i class="fas fa-spinner fa-spin mr-1" v-if="isSubmittingImport"></i>
              <i class="fas fa-file-import mr-1" v-else></i>
              បញ្ជាក់ និងរក្សាទុក ({{ validRecordsCount }} នាក់)
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- HIKVISION Management Modal -->
    <div v-if="showHikvisionModal" class="custom-modal-backdrop" @click.self="closeHikvisionModal">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable my-auto" role="document" style="width: 100%; max-width: 1100px;">
        <div class="modal-content border-0 shadow-lg rounded-lg font-khmer">
          <!-- Modal Header -->
          <div class="modal-header bg-dark text-white py-3">
            <div class="d-flex align-items-center">
              <div class="bg-warning text-dark rounded-circle p-2 mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="fas fa-fingerprint fa-lg"></i>
              </div>
              <div>
                <h5 class="modal-title font-weight-bold m-0 text-white">
                  គ្រប់គ្រងម៉ាស៊ីនស្កេនវត្តមាន HIKVISION
                </h5>
                <small class="text-white-50">ការតភ្ជាប់ម៉ាស៊ីនស្កេនផ្ទៃមុខ (MinMoe), ក្រយៅដៃ, និងកាតឆ្លាតវៃ</small>
              </div>
            </div>
            <button type="button" class="close text-white" @click="closeHikvisionModal">
              <span>&times;</span>
            </button>
          </div>

          <!-- Nav Tabs -->
          <div class="bg-light px-4 pt-3 border-bottom">
            <ul class="nav nav-tabs border-0 font-khmer">
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold cursor-pointer"
                  :class="{ 'active text-primary border-bottom-0 bg-white': activeHikvisionTab === 'devices' }"
                  @click="activeHikvisionTab = 'devices'"
                >
                  <i class="fas fa-server mr-1"></i> បញ្ជីម៉ាស៊ីនស្កេន ({{ devices.length }})
                </a>
              </li>
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold cursor-pointer"
                  :class="{ 'active text-primary border-bottom-0 bg-white': activeHikvisionTab === 'logs' }"
                  @click="switchHikvisionTab('logs')"
                >
                  <i class="fas fa-history mr-1"></i> ប្រវត្តិ Scan ជាក់ស្តែង
                </a>
              </li>
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold cursor-pointer"
                  :class="{ 'active text-primary border-bottom-0 bg-white': activeHikvisionTab === 'setup' }"
                  @click="switchHikvisionTab('setup')"
                >
                  <i class="fas fa-cogs mr-1"></i> សេចក្តីណែនាំកំណត់ម៉ាស៊ីន (Setup Guide)
                </a>
              </li>
            </ul>
          </div>

          <!-- Modal Body -->
          <div class="modal-body p-4" style="max-height: 72vh; overflow-y: auto;">
            <!-- TAB 1: DEVICES LIST -->
            <div v-if="activeHikvisionTab === 'devices'">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="font-weight-bold text-dark mb-0">
                  <i class="fas fa-network-wired text-info mr-1"></i> ម៉ាស៊ីនដែលបានភ្ជាប់ក្នុងប្រព័ន្ធ
                </h6>
                <button
                  type="button"
                  class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs font-khmer"
                  @click="openAddDeviceForm"
                >
                  <i class="fas fa-plus mr-1"></i> បន្ថែមម៉ាស៊ីនថ្មី
                </button>
              </div>

              <!-- Add/Edit Device Form Collapse/Card -->
              <div v-if="showDeviceForm" class="card border border-primary shadow-xs rounded-lg mb-4 bg-light">
                <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                  <span class="font-weight-bold text-primary font-khmer">
                    <i class="fas fa-edit mr-1"></i> {{ deviceForm.id ? 'កែប្រែព័ត៌មានម៉ាស៊ីន' : 'បន្ថែមម៉ាស៊ីន HIKVISION ថ្មី' }}
                  </span>
                  <button type="button" class="btn btn-xs btn-outline-secondary" @click="showDeviceForm = false">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
                <div class="card-body p-3 font-khmer">
                  <form @submit.prevent="saveDevice">
                    <div class="row">
                      <div class="col-md-6 mb-2">
                        <label class="font-weight-bold small text-muted">ឈ្មោះសម្គាល់ម៉ាស៊ីន: *</label>
                        <input
                          type="text"
                          class="form-control form-control-sm"
                          v-model="deviceForm.name"
                          placeholder="ឧ. ម៉ាស៊ីនច្រកចូលធំ (Main Entrance)"
                          required
                        />
                      </div>
                      <div class="col-md-6 mb-2">
                        <label class="font-weight-bold small text-muted">ម៉ូដែលម៉ាស៊ីន (Model):</label>
                        <input
                          type="text"
                          class="form-control form-control-sm"
                          v-model="deviceForm.model"
                          placeholder="ឧ. DS-K1T341 / MinMoe Face Terminal"
                        />
                      </div>
                      <div class="col-md-4 mb-2">
                        <label class="font-weight-bold small text-muted">អាសយដ្ឋាន IP (IP Address): *</label>
                        <input
                          type="text"
                          class="form-control form-control-sm"
                          v-model="deviceForm.ip_address"
                          placeholder="ឧ. 192.168.1.200"
                          required
                        />
                      </div>
                      <div class="col-md-2 mb-2">
                        <label class="font-weight-bold small text-muted">Port: *</label>
                        <input
                          type="number"
                          class="form-control form-control-sm"
                          v-model="deviceForm.port"
                          placeholder="80"
                          required
                        />
                      </div>
                      <div class="col-md-3 mb-2">
                        <label class="font-weight-bold small text-muted">Username: *</label>
                        <input
                          type="text"
                          class="form-control form-control-sm"
                          v-model="deviceForm.username"
                          placeholder="admin"
                          required
                        />
                      </div>
                      <div class="col-md-3 mb-2">
                        <label class="font-weight-bold small text-muted">Password: {{ deviceForm.id ? '(ទុកទំនេរប្រសិនបើមិនប្តូរ)' : '*' }}</label>
                        <input
                          type="password"
                          class="form-control form-control-sm"
                          v-model="deviceForm.password"
                          placeholder="••••••••"
                          :required="!deviceForm.id"
                        />
                      </div>
                    </div>
                    <div class="d-flex justify-content-end mt-2">
                      <button type="button" class="btn btn-sm btn-secondary mr-2" @click="showDeviceForm = false">បោះបង់</button>
                      <button type="submit" class="btn btn-sm btn-primary px-3" :disabled="savingDevice">
                        <i class="fas fa-spinner fa-spin mr-1" v-if="savingDevice"></i>
                        {{ deviceForm.id ? 'រក្សាទុកការកែប្រែ' : 'បន្ថែមម៉ាស៊ីន' }}
                      </button>
                    </div>
                  </form>
                </div>
              </div>

              <!-- Device Cards -->
              <div v-if="loadingDevices" class="text-center py-4 text-muted">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                <p class="small">កំពុងទាញយកបញ្ជីម៉ាស៊ីន...</p>
              </div>
              <div v-else-if="devices.length === 0" class="text-center py-5 bg-light rounded-lg border">
                <i class="fas fa-laptop-house fa-3x text-muted mb-2"></i>
                <h6 class="font-weight-bold text-dark">មិនទាន់មានម៉ាស៊ីនស្កេនត្រូវបានបន្ថែមនៅឡើយទេ</h6>
                <p class="small text-muted mb-3">សូមចុច "បន្ថែមម៉ាស៊ីនថ្មី" ដើម្បីកំណត់ IP និងគណនីម៉ាស៊ីន HIKVISION របស់អ្នក</p>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" @click="openAddDeviceForm">
                  <i class="fas fa-plus mr-1"></i> បន្ថែមម៉ាស៊ីនឥឡូវនេះ
                </button>
              </div>
              <div v-else class="row">
                <div v-for="dev in devices" :key="dev.id" class="col-md-6 mb-3">
                  <div class="card border rounded-lg h-100 shadow-xs bg-white">
                    <div class="card-body p-3">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                          <h6 class="font-weight-bold text-dark mb-0">{{ dev.name }}</h6>
                          <small class="text-muted">{{ dev.model || 'Hikvision Terminal' }}</small>
                        </div>
                        <span class="badge" :class="dev.last_status === 'ONLINE' ? 'badge-success' : (dev.last_status === 'OFFLINE' ? 'badge-danger' : 'badge-secondary')">
                          <i class="fas fa-circle mr-1 text-xs"></i>{{ dev.last_status || 'UNKNOWN' }}
                        </span>
                      </div>
                      <div class="small text-secondary mb-3">
                        <div><i class="fas fa-network-wired text-muted mr-1"></i> IP: <strong>{{ dev.ip_address }}:{{ dev.port }}</strong></div>
                        <div v-if="dev.last_sync_at"><i class="far fa-clock text-muted mr-1"></i> Sync ចុងក្រោយ: {{ formatDateTimeShort(dev.last_sync_at) }}</div>
                        <div v-if="dev.status_message" class="text-info mt-1"><i class="fas fa-info-circle mr-1"></i>{{ dev.status_message }}</div>
                      </div>
                      <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <div class="btn-group">
                          <button
                            type="button"
                            class="btn btn-xs btn-outline-success mr-1"
                            :disabled="testingDevice === dev.id"
                            @click="testDevice(dev)"
                            title="តេស្តការតភ្ជាប់តាម ISAPI"
                          >
                            <i class="fas fa-plug mr-1" :class="{ 'fa-spin': testingDevice === dev.id }"></i> តេស្ត
                          </button>
                          <button
                            type="button"
                            class="btn btn-xs btn-outline-info mr-1"
                            :disabled="syncingDeviceId === dev.id"
                            @click="syncDeviceEvents(dev)"
                            title="ទាញយកទិន្នន័យពីម៉ាស៊ីនសម្រាប់ថ្ងៃដែលបានរើស"
                          >
                            <i class="fas fa-sync mr-1" :class="{ 'fa-spin': syncingDeviceId === dev.id }"></i> Sync
                          </button>
                        </div>
                        <div>
                          <button type="button" class="btn btn-xs btn-light text-primary mr-1" @click="editDevice(dev)">
                            <i class="fas fa-edit"></i>
                          </button>
                          <button type="button" class="btn btn-xs btn-light text-danger" @click="confirmDeleteDevice(dev)">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- TAB 2: REAL-TIME DEVICE LOGS -->
            <div v-if="activeHikvisionTab === 'logs'">
              <!-- Filter Bar -->
              <div class="row align-items-center mb-3">
                <div class="col-md-4 mb-2 mb-md-0">
                  <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                      <span class="input-group-text bg-white"><i class="far fa-calendar-alt text-muted"></i></span>
                    </div>
                    <input type="date" class="form-control" v-model="logsFilterDate" @change="fetchDeviceLogs" />
                  </div>
                </div>
                <div class="col-md-5 mb-2 mb-md-0">
                  <div class="input-group input-group-sm">
                    <input
                      type="text"
                      class="form-control"
                      v-model="logsSearchQuery"
                      placeholder="ស្វែងរកតាមកូដ ឬឈ្មោះ..."
                      @keyup.enter="fetchDeviceLogs"
                    />
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" @click="fetchDeviceLogs"><i class="fas fa-search"></i></button>
                    </div>
                  </div>
                </div>
                <div class="col-md-3 text-md-right">
                  <button type="button" class="btn btn-sm btn-outline-primary" @click="fetchDeviceLogs">
                    <i class="fas fa-redo-alt mr-1"></i> Refresh Logs
                  </button>
                </div>
              </div>

              <!-- Logs Table -->
              <div class="table-responsive border rounded bg-white" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-hover align-middle mb-0 font-khmer">
                  <thead class="bg-light sticky-top small text-muted">
                    <tr>
                      <th style="width: 50px;" class="text-center">#</th>
                      <th>ពេលវេលា Scan</th>
                      <th>មន្ត្រី</th>
                      <th>កូដម៉ាស៊ីន</th>
                      <th class="text-center">វិធី Scan</th>
                      <th>ម៉ាស៊ីន</th>
                      <th class="text-center">ស្ថានភាព</th>
                      <th>សម្គាល់</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="loadingLogs">
                      <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-spinner fa-spin mr-1"></i> កំពុងទាញយក Logs...
                      </td>
                    </tr>
                    <tr v-else-if="deviceLogs.length === 0">
                      <td colspan="8" class="text-center py-4 text-muted">
                        មិនមានទិន្នន័យ Scan សម្រាប់ថ្ងៃនេះឡើយ
                      </td>
                    </tr>
                    <tr v-for="(log, idx) in deviceLogs" :key="log.id">
                      <td class="text-center text-muted font-weight-bold">{{ idx + 1 }}</td>
                      <td>
                        <strong class="text-dark">{{ formatDateTimeShort(log.scan_time) }}</strong>
                      </td>
                      <td>
                        <div v-if="log.user" class="d-flex align-items-center">
                          <img
                            :src="getFullImageUrl(log.user.profile_image)"
                            class="img-circle border mr-2"
                            style="width: 30px; height: 30px; object-fit: cover;"
                          />
                          <div>
                            <span class="font-weight-bold text-dark small d-block">{{ log.user.name_kh || log.user.name }}</span>
                            <small class="text-muted">{{ log.user.position?.title_kh || 'មន្ត្រី' }}</small>
                          </div>
                        </div>
                        <span v-else class="text-muted small">---</span>
                      </td>
                      <td>
                        <span class="badge badge-light border">{{ log.employee_no }}</span>
                      </td>
                      <td class="text-center">
                        <span class="badge" :class="getVerifyModeBadge(log.verify_mode)">
                          <i :class="getVerifyModeIcon(log.verify_mode)" class="mr-1"></i>
                          {{ formatVerifyMode(log.verify_mode) }}
                        </span>
                      </td>
                      <td>
                        <small class="text-secondary">{{ log.device?.name || log.ip_address || '---' }}</small>
                      </td>
                      <td class="text-center">
                        <span class="badge" :class="log.status === 'PROCESSED' ? 'badge-success' : 'badge-danger'">
                          {{ log.status === 'PROCESSED' ? 'ជោគជ័យ' : 'មិនផ្គូផ្គង' }}
                        </span>
                      </td>
                      <td>
                        <small class="text-muted text-truncate d-block" style="max-width: 180px;" :title="log.note">
                          {{ log.note || '---' }}
                        </small>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- TAB 3: SETUP INSTRUCTIONS & WEBHOOK GUIDE -->
            <div v-if="activeHikvisionTab === 'setup'">
              <div class="alert alert-primary shadow-xs rounded-lg mb-4">
                <h6 class="font-weight-bold mb-1">
                  <i class="fas fa-link mr-1"></i> Webhook URL សម្រាប់កំណត់ក្នុងម៉ាស៊ីន HIKVISION (HTTP Listening / Event Push)
                </h6>
                <p class="small text-muted mb-2">
                  សូមចម្លង (Copy) Link ខាងក្រោមនេះ ទៅដាក់ក្នុង Web Management របស់ម៉ាស៊ីន Hikvision ដើម្បីឱ្យម៉ាស៊ីន Push ទិន្នន័យវត្តមានមក TRMS ភ្លាមៗរាល់ពេលមន្ត្រី Scan៖
                </p>
                <div class="input-group">
                  <input
                    type="text"
                    class="form-control font-weight-bold text-dark bg-white"
                    :value="setupInfo.webhook_url || defaultWebhookUrl"
                    readonly
                  />
                  <div class="input-group-append">
                    <button class="btn btn-primary px-3" @click="copyWebhookUrl">
                      <i class="fas fa-copy mr-1"></i> ចម្លង (Copy)
                    </button>
                  </div>
                </div>
              </div>

              <!-- Steps Guide -->
              <div class="card border rounded-lg bg-light p-3 mb-3">
                <h6 class="font-weight-bold text-dark mb-3">
                  <i class="fas fa-list-ol text-success mr-2"></i> ជំហាននៃការកំណត់នៅលើ Web Management របស់ម៉ាស៊ីន HIKVISION
                </h6>
                <div class="step-item mb-3">
                  <strong class="text-primary">ជំហានទី ១៖</strong> បើក Browser (Chrome/Edge) រួចវាយ IP Address របស់ម៉ាស៊ីន Hikvision (ឧ. <code>http://192.168.1.200</code>) ហើយ Login ជាមួយ user <code>admin</code>។
                </div>
                <div class="step-item mb-3">
                  <strong class="text-primary">ជំហានទី ២៖</strong> ចូលទៅកាន់ Menu:
                  <br><code>Configuration -> Network -> Advanced Configuration -> HTTP Listening</code> (ឬម៉ាស៊ីនខ្លះនៅត្រង់ <code>Alarm -> Event Linkage -> HTTP Host</code>)។
                </div>
                <div class="step-item mb-3">
                  <strong class="text-primary">ជំហានទី ៣៖</strong> បំពេញព័ត៌មានខាងក្រោម៖
                  <ul>
                    <li><strong>Destination IP / Domain:</strong> IP Address ឬ Domain របស់ Server TRMS</li>
                    <li><strong>Port:</strong> Port របស់ Server (ឧ. <code>80</code> ឬ <code>8000</code>)</li>
                    <li><strong>URL:</strong> <code>/api/attendance/hikvision/event</code></li>
                  </ul>
                </div>
                <div class="step-item mb-3">
                  <strong class="text-primary">ជំហានទី ៤ (សំខាន់បំផុត - ការផ្គូផ្គងមន្ត្រី):</strong>
                  <br>នៅពេលលោកអ្នកចុះឈ្មោះមន្ត្រី (User Management) លើម៉ាស៊ីន Hikvision សូមបញ្ចូល <strong>Employee ID</strong> ឱ្យដូចគ្នាទៅនឹង <strong>«កូដមន្ត្រី (employee_code)»</strong> ឬ <strong>«ID»</strong> របស់មន្ត្រីក្នុងប្រព័ន្ធ TRMS។
                  <br><small class="text-muted">ឧទាហរណ៍៖ បើមន្ត្រីមានកូដក្នុង TRMS ជា <code>EMP001</code> នោះ Employee ID លើម៉ាស៊ីនត្រូវដាក់ <code>EMP001</code> ដូចគ្នា។</small>
                </div>
              </div>
            </div>

          </div>

          <!-- Modal Footer -->
          <div class="modal-footer bg-light justify-content-between">
            <span class="text-muted small">
              <i class="fas fa-shield-alt text-success mr-1"></i> គាំទ្រទាំងមុខងារ Webhook Push និង ISAPI Sync
            </span>
            <button type="button" class="btn btn-secondary px-4 rounded-pill" @click="closeHikvisionModal">
              បិទផ្ទាំង
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import $ from 'jquery';
import Swal from 'sweetalert2';
import * as XLSX from 'xlsx';
import { apiGetAttendances, apiSaveAttendance, apiImportAttendances } from '@/functions/api/attendance';
import {
  apiGetBiometricDevices,
  apiCreateBiometricDevice,
  apiUpdateBiometricDevice,
  apiDeleteBiometricDevice,
  apiTestBiometricDeviceConnection,
  apiSyncBiometricDevice,
  apiGetBiometricDeviceLogs,
  apiGetBiometricSetupInfo
} from '@/functions/api/biometricDevice';

const attendances = ref([]);
const searchQuery = ref('');
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const attendanceModal = ref(null);
const form = reactive({ user_id: null, date: '', status: 'PRESENT', check_in_time: '', check_out_time: '', note: '' });

const filteredAttendances = computed(() => {
  if (!searchQuery.value.trim()) {
    return attendances.value;
  }
  const q = searchQuery.value.toLowerCase().trim();
  return attendances.value.filter(item => {
    const nameKh = (item.user?.name_kh || '').toLowerCase();
    const nameEn = (item.user?.name_en || '').toLowerCase();
    const name = (item.user?.name || '').toLowerCase();
    return nameKh.includes(q) || nameEn.includes(q) || name.includes(q);
  });
});

const fetchAttendances = async () => {
  const res = await apiGetAttendances({ date: selectedDate.value });
  let data = res.data.data;
  
  data.sort((a, b) => {
    const levelA = a.user?.position?.level ?? 99;
    const levelB = b.user?.position?.level ?? 99;
    return levelA - levelB;
  });

  // បង្ខំឱ្យ Vue Update Component ថ្មីដោយប្រើ Spread Operator (...)
  attendances.value = [...data];
};

const handleSpecialStatus = (item, status) => {
  if (item.status === status) {
    const payload = { user_id: item.user_id, date: selectedDate.value, status: 'PRESENT', check_in_time: '08:00', note: '' };
    apiSaveAttendance(payload).then(() => fetchAttendances());
  } else {
    form.user_id = item.user_id;
    form.date = selectedDate.value;
    form.status = status;
    form.check_in_time = null;
    form.check_out_time = null;
    form.note = '';
    $(attendanceModal.value).modal('show');
  }
};

const openModal = (item) => {
  form.user_id = item.user_id;
  form.date = selectedDate.value;
  form.status = ['ABSENT', 'PERMISSION', 'MISSION'].includes(item.status) ? 'PRESENT' : item.status;
  form.check_in_time = item.check_in_time || '08:00';
  form.check_out_time = item.check_out_time || '';
  form.note = item.note || '';
  $(attendanceModal.value).modal('show');
};

const closeModal = () => $(attendanceModal.value).modal('hide');

const saveAttendance = async () => {
  if (form.check_in_time) {
    // កាត់យកតែ ៥ តួដំបូង (HH:mm) ឧ. "09:00:15" 变为 "09:00"
    const timeOnly = form.check_in_time.substring(0, 5);

    if (timeOnly > '09:00') {
      form.status = 'LATE';       
    } else {
      form.status = 'PRESENT';
    }
  }

  await apiSaveAttendance(form);
  closeModal();
  fetchAttendances();
  Swal.fire({ icon: 'success', title: 'រក្សាទុកជោគជ័យ!', showConfirmButton: false, timer: 1500 });
};

const formatStatusKhmer = (s) => ({'PRESENT':'មានវត្តមាន','LATE':'មកយឺត','ABSENT':'អវត្តមាន','PERMISSION':'ច្បាប់','MISSION':'បេសកកម្ម'}[s] || s);

// គណនាម៉ោងធ្វើការ
const calculateWorkingHours = (checkIn, checkOut, status) => {
  if (!['PRESENT', 'LATE'].includes(status)) return null;
  if (!checkIn || !checkOut) return null;

  try {
    const inParts = String(checkIn).split(':').map(Number);
    const outParts = String(checkOut).split(':').map(Number);
    const inH = inParts[0], inM = inParts[1] || 0;
    const outH = outParts[0], outM = outParts[1] || 0;

    if (isNaN(inH) || isNaN(inM) || isNaN(outH) || isNaN(outM)) return null;

    const startMinutes = inH * 60 + inM;
    const endMinutes = outH * 60 + outM;

    if (endMinutes <= startMinutes) return null;

    const diffMinutes = endMinutes - startMinutes;
    const hours = Math.floor(diffMinutes / 60);
    const mins = diffMinutes % 60;

    if (mins === 0) {
      return `${hours} ម៉ោង`;
    }
    return `${hours} ម៉ោង ${mins} នាទី`;
  } catch (e) {
    return null;
  }
};

const getWorkingHoursDisplay = (item) => {
  if (item.working_hours_formatted) {
    return item.working_hours_formatted;
  }
  return calculateWorkingHours(item.check_in_time, item.check_out_time, item.status);
};

const modalCalculatedWorkingHours = computed(() => {
  return calculateWorkingHours(form.check_in_time, form.check_out_time, form.status);
});

// ========================
// EXCEL IMPORT FUNCTIONALITY
// ========================
const showImportModal = ref(false);
const fileInputRef = ref(null);
const importFileName = ref('');
const importingFile = ref(false);
const isSubmittingImport = ref(false);
const parsedRecords = ref([]);

const validRecordsCount = computed(() => parsedRecords.value.filter(r => r.isValid).length);
const invalidRecordsCount = computed(() => parsedRecords.value.filter(r => !r.isValid).length);

const openImportModal = () => {
  importFileName.value = '';
  parsedRecords.value = [];
  importingFile.value = false;
  isSubmittingImport.value = false;
  showImportModal.value = true;
};

const closeImportModal = () => {
  showImportModal.value = false;
};

const triggerFileInput = () => {
  if (fileInputRef.value) {
    fileInputRef.value.click();
  }
};

const resetFileSelection = () => {
  importFileName.value = '';
  parsedRecords.value = [];
  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
};

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'PRESENT': return 'badge-success';
    case 'LATE': return 'badge-warning';
    case 'ABSENT': return 'badge-danger';
    case 'PERMISSION': return 'badge-info';
    case 'MISSION': return 'badge-primary';
    default: return 'badge-secondary';
  }
};

// 1. Download Sample Excel Template (.xlsx)
const downloadExcelTemplate = () => {
  const data = attendances.value.map(item => ({
    'employee_code': item.user?.employee_code || (item.user?.id ? `ID-${item.user.id}` : ''),
    'name': item.user?.name_kh || item.user?.name || '',
    'date': selectedDate.value,
    'status': item.status || 'PRESENT',
    'check_in_time': item.check_in_time || '08:00',
    'check_out_time': item.check_out_time || '17:00',
    'note': item.note || ''
  }));

  if (data.length === 0) {
    data.push({
      'employee_code': 'EMP001',
      'name': 'ឈ្មោះមន្ត្រីគំរូ',
      'date': selectedDate.value,
      'status': 'PRESENT',
      'check_in_time': '08:00',
      'check_out_time': '17:00',
      'note': ''
    });
  }

  const worksheet = XLSX.utils.json_to_sheet(data);
  worksheet['!cols'] = [
    { wch: 18 }, // employee_code
    { wch: 26 }, // name
    { wch: 14 }, // date
    { wch: 16 }, // status
    { wch: 14 }, // check_in_time
    { wch: 14 }, // check_out_time
    { wch: 30 }  // note
  ];

  const workbook = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Attendance');
  XLSX.writeFile(workbook, `Attendance_Template_${selectedDate.value}.xlsx`);
};

// 2. Parse uploaded Excel file
const handleFileSelected = (event) => {
  const file = event.target.files[0];
  if (!file) return;

  importFileName.value = file.name;
  importingFile.value = true;
  parsedRecords.value = [];

  const reader = new FileReader();
  reader.onload = (e) => {
    try {
      const data = new Uint8Array(e.target.result);
      const workbook = XLSX.read(data, { type: 'array' });
      const firstSheetName = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[firstSheetName];
      const rawJson = XLSX.utils.sheet_to_json(worksheet, { defval: '' });

      const records = [];

      rawJson.forEach((row, index) => {
        const empCode = String(row['employee_code'] || row['កូដមន្ត្រី'] || row['Code'] || row['code'] || '').trim();
        const rowName = String(row['name'] || row['ឈ្មោះ'] || row['ឈ្មោះមន្ត្រី'] || '').trim();
        let rowDate = String(row['date'] || row['កាលបរិច្ឆេទ'] || selectedDate.value).trim();

        // Handle Excel numeric date serial
        if (typeof row['date'] === 'number') {
          const jsDate = new Date(Math.round((row['date'] - 25569) * 86400 * 1000));
          rowDate = jsDate.toISOString().split('T')[0];
        }

        let rowStatus = String(row['status'] || row['ស្ថានភាព'] || 'PRESENT').trim().toUpperCase();
        if (rowStatus === 'មានវត្តមាន' || rowStatus === 'វត្តមាន') rowStatus = 'PRESENT';
        else if (rowStatus === 'មកយឺត' || rowStatus === 'យឺត') rowStatus = 'LATE';
        else if (rowStatus === 'អវត្តមាន') rowStatus = 'ABSENT';
        else if (rowStatus === 'ច្បាប់' || rowStatus === 'មានច្បាប់') rowStatus = 'PERMISSION';
        else if (rowStatus === 'បេសកកម្ម') rowStatus = 'MISSION';

        let checkIn = String(row['check_in_time'] || row['ម៉ោងចូល'] || '').trim();
        let checkOut = String(row['check_out_time'] || row['ម៉ោងចេញ'] || '').trim();
        const note = String(row['note'] || row['មូលហេតុ'] || row['សម្គាល់'] || '').trim();

        // Convert Excel fractional time if any
        if (typeof row['check_in_time'] === 'number') {
          const totalMinutes = Math.round(row['check_in_time'] * 24 * 60);
          const h = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
          const m = String(totalMinutes % 60).padStart(2, '0');
          checkIn = `${h}:${m}`;
        }
        if (typeof row['check_out_time'] === 'number') {
          const totalMinutes = Math.round(row['check_out_time'] * 24 * 60);
          const h = String(Math.floor(totalMinutes / 60)).padStart(2, '0');
          const m = String(totalMinutes % 60).padStart(2, '0');
          checkOut = `${h}:${m}`;
        }

        // Match user from current attendances list
        const matchedItem = attendances.value.find(item => {
          const u = item.user;
          if (!u) return false;
          if (empCode) {
            if (String(u.employee_code).toLowerCase() === empCode.toLowerCase()) return true;
            if (String(u.id) === empCode || `ID-${u.id}`.toLowerCase() === empCode.toLowerCase()) return true;
          }
          if (rowName) {
            if (String(u.name_kh).toLowerCase() === rowName.toLowerCase()) return true;
            if (String(u.name).toLowerCase() === rowName.toLowerCase()) return true;
          }
          return false;
        });

        const isValid = !!matchedItem;

        records.push({
          row_index: index + 1,
          employee_code: empCode,
          name: matchedItem ? (matchedItem.user?.name_kh || matchedItem.user?.name) : rowName,
          user_id: matchedItem ? matchedItem.user_id : null,
          date: rowDate || selectedDate.value,
          status: rowStatus,
          check_in_time: checkIn,
          check_out_time: checkOut,
          note: note,
          isValid: isValid,
          errorMessage: isValid ? '' : 'រកមិនឃើញមន្ត្រីតាមកូដ ឬឈ្មោះនេះឡើយ'
        });
      });

      parsedRecords.value = records;
    } catch (err) {
      console.error('Failed to parse Excel file:', err);
      Swal.fire('កំហុស', 'មិនអាចអាន File Excel នេះបានឡើយ សូមពិនិត្យមើលទម្រង់ File ម្តងទៀត', 'error');
    } finally {
      importingFile.value = false;
    }
  };

  reader.readAsArrayBuffer(file);
};

// 3. Confirm and Send to API
const confirmImport = async () => {
  const validRecords = parsedRecords.value.filter(r => r.isValid && r.user_id);
  if (validRecords.length === 0) {
    Swal.fire('គ្មានទិន្នន័យ', 'មិនមានទិន្នន័យត្រឹមត្រូវសម្រាប់ Import ឡើយ', 'warning');
    return;
  }

  isSubmittingImport.value = true;
  try {
    const payload = {
      records: validRecords.map(r => ({
        user_id: r.user_id,
        date: r.date,
        status: r.status,
        check_in_time: r.check_in_time || null,
        check_out_time: r.check_out_time || null,
        note: r.note || null
      }))
    };

    const res = await apiImportAttendances(payload);
    if (res.data?.success) {
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ!',
        text: res.data.message || `បាន Import វត្តមានដោយជោគជ័យ`,
        timer: 2000,
        showConfirmButton: false
      });
      closeImportModal();
      await fetchAttendances();
    } else {
      Swal.fire('កំហុស', res.data?.message || 'មានបញ្ហាក្នុងការ Import វត្តមាន', 'error');
    }
  } catch (err) {
    console.error('Error importing attendances:', err);
    Swal.fire('កំហុស', err.response?.data?.message || 'បរាជ័យក្នុងការ Import វត្តមាន', 'error');
  } finally {
    isSubmittingImport.value = false;
  }
};

// ==========================================
// HIKVISION BIOMETRIC SCANNER INTEGRATION
// ==========================================
const showHikvisionModal = ref(false);
const activeHikvisionTab = ref('devices');
const devices = ref([]);
const loadingDevices = ref(false);
const deviceLogs = ref([]);
const loadingLogs = ref(false);
const logsFilterDate = ref(selectedDate.value);
const logsSearchQuery = ref('');
const setupInfo = ref({});
const showDeviceForm = ref(false);
const savingDevice = ref(false);
const testingDevice = ref(null);
const syncingDeviceId = ref(null);
const syncingDevice = ref(false);

const deviceForm = reactive({
  id: null,
  name: '',
  model: '',
  ip_address: '',
  port: 80,
  username: 'admin',
  password: '',
  protocol: 'HTTP',
  is_active: true
});

const defaultAvatar = 'https://adminlte.io/themes/v3/dist/img/user2-160x160.jpg';

const defaultWebhookUrl = computed(() => {
  const base = (import.meta.env.VITE_APP_API_URL || 'http://localhost:8000').replace(/\/api\/?$/, '');
  return `${base}/api/attendance/hikvision/event`;
});

const getFullImageUrl = (path) => {
  if (!path) return defaultAvatar;
  if (path.startsWith('http')) return path;
  const backendBase = (import.meta.env.VITE_APP_API_URL || 'http://localhost:8000').replace(/\/api\/?$/, '');
  let cleanPath = path.replace(/^\//, '');
  if (!cleanPath.startsWith('storage/')) cleanPath = `storage/${cleanPath}`;
  return `${backendBase}/${cleanPath}`;
};

const formatDateTimeShort = (dtStr) => {
  if (!dtStr) return '---';
  try {
    const d = new Date(dtStr);
    const date = d.toLocaleDateString('en-GB');
    const time = d.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    return `${date} ${time}`;
  } catch (e) {
    return dtStr;
  }
};

const formatVerifyMode = (mode) => {
  const m = String(mode || '').toLowerCase();
  if (m.includes('face')) return 'ផ្ទៃមុខ';
  if (m.includes('finger')) return 'ក្រយៅដៃ';
  if (m.includes('card')) return 'កាត';
  if (m.includes('password') || m.includes('psw')) return 'លេខកូដ';
  return 'ម៉ាស៊ីន';
};

const getVerifyModeBadge = (mode) => {
  const m = String(mode || '').toLowerCase();
  if (m.includes('face')) return 'badge-primary';
  if (m.includes('finger')) return 'badge-success';
  if (m.includes('card')) return 'badge-info';
  return 'badge-secondary';
};

const getVerifyModeIcon = (mode) => {
  const m = String(mode || '').toLowerCase();
  if (m.includes('face')) return 'fas fa-smile';
  if (m.includes('finger')) return 'fas fa-fingerprint';
  if (m.includes('card')) return 'fas fa-id-card';
  return 'fas fa-shield-alt';
};

const fetchDevices = async () => {
  loadingDevices.value = true;
  try {
    const res = await apiGetBiometricDevices();
    if (res.data?.success) {
      devices.value = res.data.devices || [];
    }
  } catch (e) {
    console.error('Failed to fetch biometric devices:', e);
  } finally {
    loadingDevices.value = false;
  }
};

const fetchDeviceLogs = async () => {
  loadingLogs.value = true;
  try {
    const params = {
      date: logsFilterDate.value || undefined,
      search: logsSearchQuery.value || undefined,
    };
    const res = await apiGetBiometricDeviceLogs(params);
    if (res.data?.success) {
      deviceLogs.value = res.data.data?.data || res.data.data || [];
    }
  } catch (e) {
    console.error('Failed to fetch device logs:', e);
  } finally {
    loadingLogs.value = false;
  }
};

const fetchSetupInfo = async () => {
  try {
    const res = await apiGetBiometricSetupInfo();
    if (res.data?.success) {
      setupInfo.value = res.data;
    }
  } catch (e) {
    console.error('Failed to fetch setup info:', e);
  }
};

const openHikvisionModal = async () => {
  showHikvisionModal.value = true;
  activeHikvisionTab.value = 'devices';
  await fetchDevices();
  await fetchSetupInfo();
};

const closeHikvisionModal = () => {
  showHikvisionModal.value = false;
};

const switchHikvisionTab = (tab) => {
  activeHikvisionTab.value = tab;
  if (tab === 'logs') {
    logsFilterDate.value = selectedDate.value;
    fetchDeviceLogs();
  } else if (tab === 'setup') {
    fetchSetupInfo();
  } else if (tab === 'devices') {
    fetchDevices();
  }
};

const openAddDeviceForm = () => {
  deviceForm.id = null;
  deviceForm.name = '';
  deviceForm.model = '';
  deviceForm.ip_address = '';
  deviceForm.port = 80;
  deviceForm.username = 'admin';
  deviceForm.password = '';
  deviceForm.protocol = 'HTTP';
  deviceForm.is_active = true;
  showDeviceForm.value = true;
};

const editDevice = (dev) => {
  deviceForm.id = dev.id;
  deviceForm.name = dev.name;
  deviceForm.model = dev.model || '';
  deviceForm.ip_address = dev.ip_address;
  deviceForm.port = dev.port || 80;
  deviceForm.username = dev.username || 'admin';
  deviceForm.password = '';
  deviceForm.protocol = dev.protocol || 'HTTP';
  deviceForm.is_active = dev.is_active;
  showDeviceForm.value = true;
};

const saveDevice = async () => {
  savingDevice.value = true;
  try {
    let res;
    if (deviceForm.id) {
      res = await apiUpdateBiometricDevice(deviceForm.id, deviceForm);
    } else {
      res = await apiCreateBiometricDevice(deviceForm);
    }

    if (res.data?.success) {
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ!',
        text: res.data.message || 'បានរក្សាទុកព័ត៌មានម៉ាស៊ីន',
        timer: 1800,
        showConfirmButton: false,
      });
      showDeviceForm.value = false;
      await fetchDevices();
    }
  } catch (e) {
    console.error('Failed to save device:', e);
    Swal.fire('កំហុស', e.response?.data?.message || 'បរាជ័យក្នុងការរក្សាទុកព័ត៌មានម៉ាស៊ីន', 'error');
  } finally {
    savingDevice.value = false;
  }
};

const confirmDeleteDevice = async (dev) => {
  const result = await Swal.fire({
    title: 'តើអ្នកប្រាកដទេ?',
    text: `តើអ្នកពិតជាចង់លុបម៉ាស៊ីន "${dev.name}" នេះមែនទេ?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'បាទ/ចាស លុបចេញ',
    cancelButtonText: 'បោះបង់'
  });

  if (result.isConfirmed) {
    try {
      const res = await apiDeleteBiometricDevice(dev.id);
      if (res.data?.success) {
        Swal.fire('បានលុប!', 'ម៉ាស៊ីនត្រូវបានលុបចេញពីប្រព័ន្ធរួចរាល់', 'success');
        await fetchDevices();
      }
    } catch (e) {
      Swal.fire('កំហុស', 'មិនអាចលុបម៉ាស៊ីននេះបានទេ', 'error');
    }
  }
};

const testDevice = async (dev) => {
  testingDevice.value = dev.id;
  try {
    const res = await apiTestBiometricDeviceConnection(dev.id);
    if (res.data?.success) {
      Swal.fire({
        icon: 'success',
        title: 'តភ្ជាប់ជោគជ័យ!',
        html: `<b>${res.data.message}</b><br><small class="text-muted">ម៉ូដែល: ${res.data.device_info?.model || 'Hikvision'} | ស៊េរី: ${res.data.device_info?.serial_number || '---'}</small>`,
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'មិនអាចតភ្ជាប់បានទេ!',
        text: res.data?.message || 'សូមពិនិត្យ IP Address, Port និង Password របស់ម៉ាស៊ីន',
      });
    }
    await fetchDevices();
  } catch (e) {
    Swal.fire('កំហុស', 'បរាជ័យក្នុងការតេស្តការតភ្ជាប់', 'error');
  } finally {
    testingDevice.value = null;
  }
};

const syncDeviceEvents = async (dev) => {
  syncingDeviceId.value = dev.id;
  try {
    const res = await apiSyncBiometricDevice(dev.id, selectedDate.value);
    if (res.data?.success) {
      Swal.fire({
        icon: 'success',
        title: 'Sync ជោគជ័យ!',
        text: res.data.message,
        timer: 2500,
        showConfirmButton: false,
      });
      await fetchAttendances();
      await fetchDevices();
    } else {
      Swal.fire('កំហុស', res.data?.message || 'បរាជ័យក្នុងការ Sync វត្តមាន', 'error');
    }
  } catch (e) {
    Swal.fire('កំហុស', 'មានបញ្ហាក្នុងការទាញយកទិន្នន័យពីម៉ាស៊ីន', 'error');
  } finally {
    syncingDeviceId.value = null;
  }
};

const triggerQuickDeviceSync = async () => {
  if (devices.value.length === 0) {
    await fetchDevices();
  }
  if (devices.value.length === 0) {
    Swal.fire('មិនទាន់មានម៉ាស៊ីន', 'សូមបន្ថែមម៉ាស៊ីនស្កេន HIKVISION ជាមុនសិន', 'info');
    openHikvisionModal();
    return;
  }

  syncingDevice.value = true;
  try {
    let successCount = 0;
    for (const dev of devices.value) {
      if (!dev.is_active) continue;
      const res = await apiSyncBiometricDevice(dev.id, selectedDate.value);
      if (res.data?.success) {
        successCount++;
      }
    }
    Swal.fire({
      icon: 'success',
      title: 'បាន Sync រួចរាល់!',
      text: `បានទាញយកទិន្នន័យវត្តមានពីម៉ាស៊ីន HIKVISION សម្រាប់ថ្ងៃ ${selectedDate.value}`,
      timer: 2000,
      showConfirmButton: false,
    });
    await fetchAttendances();
  } catch (e) {
    Swal.fire('កំហុស', 'បរាជ័យក្នុងការ Sync វត្តមានពីម៉ាស៊ីន', 'error');
  } finally {
    syncingDevice.value = false;
  }
};

const copyWebhookUrl = () => {
  const url = setupInfo.value?.webhook_url || defaultWebhookUrl.value;
  navigator.clipboard.writeText(url).then(() => {
    Swal.fire({
      icon: 'success',
      title: 'បានចម្លង (Copied)!',
      text: 'Webhook URL ត្រូវបានចម្លងទៅ Clipboard រួចរាល់',
      timer: 1500,
      showConfirmButton: false,
    });
  }).catch(() => {
    Swal.fire('កំហុស', 'មិនអាចចម្លងបានទេ សូមជ្រើសរើស Text និង Copy ដោយដៃ', 'error');
  });
};

onMounted(async () => {
  await fetchAttendances();
  await fetchDevices();
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Battambang:wght@300;400;700&display=swap');
.attendance-page, .modal, .card, table, button, input, textarea { font-family: 'Battambang', sans-serif !important; }
.custom-checkbox { width: 20px; height: 20px; cursor: pointer; }
.checkbox-danger { accent-color: #dc3545; }
.checkbox-info { accent-color: #17a2b8; }
.checkbox-primary { accent-color: #007bff; }

.custom-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  overflow-y: auto;
  padding: 1rem;
}

.border-dashed {
  border: 2px dashed #28a745 !important;
  background-color: #f8fff9 !important;
  transition: all 0.2s ease;
}
.border-dashed:hover {
  background-color: #f0fff2 !important;
  border-color: #218838 !important;
}
.cursor-pointer {
  cursor: pointer;
}
.font-weight-500 {
  font-weight: 500;
}
</style>
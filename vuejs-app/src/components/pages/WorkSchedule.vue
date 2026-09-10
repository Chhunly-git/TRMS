<template>
  <div class="content-wrapper" style="min-height: 900px;">
    <!-- Content Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0 font-khmer text-dark font-weight-bold">
              <i class="fas fa-calendar-alt text-primary mr-2"></i> កាលវិភាគការងារ និងកិច្ចប្រជុំ
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right font-khmer">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">ទំព័រដើម</router-link>
              </li>
              <li class="breadcrumb-item active">កាលវិភាគការងារ</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="content font-khmer">
      <div class="container-fluid">
        <!-- 1. Statistics Cards -->
        <div class="row mb-3">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-info shadow-sm rounded-lg">
              <div class="inner">
                <h3>{{ summaryStats.total }}</h3>
                <p class="font-weight-bold mb-0">កាលវិភាគសរុបប្រចាំខែ</p>
                <small class="text-white-50">{{ formatKhmerMonthYear(currentYear, currentMonth) }}</small>
              </div>
              <div class="icon">
                <i class="fas fa-calendar-alt"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-primary shadow-sm rounded-lg">
              <div class="inner">
                <h3>{{ summaryStats.meetings }}</h3>
                <p class="font-weight-bold mb-0">កិច្ចប្រជុំ (Meetings)</p>
                <small class="text-white-50">រួមទាំង Online & ផ្ទាល់</small>
              </div>
              <div class="icon">
                <i class="fas fa-video"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning shadow-sm rounded-lg">
              <div class="inner">
                <h3 class="text-dark">{{ summaryStats.missions }}</h3>
                <p class="font-weight-bold mb-0 text-dark">បេសកកម្ម & សិក្ខាសាលា</p>
                <small class="text-dark-50">ការងារក្រៅទីតាំង/បណ្តុះបណ្តាល</small>
              </div>
              <div class="icon">
                <i class="fas fa-briefcase"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success shadow-sm rounded-lg">
              <div class="inner">
                <h3>{{ summaryStats.completed }}</h3>
                <p class="font-weight-bold mb-0">បានបញ្ចប់រួចរាល់</p>
                <small class="text-white-50">កិច្ចការដែលបានបំពេញ</small>
              </div>
              <div class="icon">
                <i class="fas fa-check-circle"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Action Bar & Filter Controls -->
        <div class="card shadow-sm border-0 mb-3 rounded-lg">
          <div class="card-body p-3">
            <div class="row align-items-center">
              <!-- Left: Month Navigator -->
              <div class="col-xl-4 col-lg-5 col-md-6 mb-2 mb-md-0 d-flex align-items-center">
                <div class="btn-group mr-2 shadow-sm">
                  <button class="btn btn-outline-secondary btn-sm" @click="prevMonth" title="ខែមុន">
                    <i class="fas fa-chevron-left"></i>
                  </button>
                  <button class="btn btn-outline-secondary btn-sm px-2 font-weight-bold" @click="goToToday" title="ទៅកាន់ខែបច្ចុប្បន្ន">
                    ថ្ងៃនេះ
                  </button>
                  <button class="btn btn-outline-secondary btn-sm" @click="nextMonth" title="ខែបន្ទាប់">
                    <i class="fas fa-chevron-right"></i>
                  </button>
                </div>
                <h4 class="mb-0 font-weight-bold text-primary text-truncate">
                  {{ formatKhmerMonthYear(currentYear, currentMonth) }}
                </h4>
              </div>

              <!-- Center & Right: View Switcher, Filter & Add Button -->
              <div class="col-xl-8 col-lg-7 col-md-6 d-flex flex-wrap justify-content-md-end align-items-center">
                <!-- Search -->
                <div class="input-group input-group-sm mr-2 mb-2 mb-sm-0" style="max-width: 180px;">
                  <input
                    type="text"
                    class="form-control"
                    placeholder="ស្វែងរក..."
                    v-model="searchKeyword"
                    @keyup.enter="fetchSchedules"
                  />
                  <div class="input-group-append">
                    <button class="btn btn-outline-secondary" @click="fetchSchedules">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
                </div>

                <!-- Type Filter -->
                <select
                  class="custom-select custom-select-sm mr-2 mb-2 mb-sm-0"
                  style="width: 130px;"
                  v-model="filterType"
                  @change="fetchSchedules"
                >
                  <option value="">គ្រប់ប្រភេទ</option>
                  <option value="meeting">កិច្ចប្រជុំ</option>
                  <option value="mission">បេសកកម្ម</option>
                  <option value="workshop">សិក្ខាសាលា</option>
                  <option value="task">ការងារទូទៅ</option>
                  <option value="appointment">ការណាត់ជួប</option>
                  <option value="other">ផ្សេងៗ</option>
                </select>

                <!-- Status Filter -->
                <select
                  class="custom-select custom-select-sm mr-2 mb-2 mb-sm-0"
                  style="width: 130px;"
                  v-model="filterStatus"
                  @change="fetchSchedules"
                >
                  <option value="">គ្រប់ស្ថានភាព</option>
                  <option value="scheduled">គ្រោងទុក</option>
                  <option value="in_progress">កំពុងដំណើរការ</option>
                  <option value="completed">បានបញ្ចប់</option>
                  <option value="cancelled">បានលុបចោល</option>
                </select>

                <!-- View Switcher -->
                <div class="btn-group btn-group-sm mr-2 mb-2 mb-sm-0 shadow-sm">
                  <button
                    class="btn"
                    :class="viewMode === 'calendar' ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="viewMode = 'calendar'"
                    title="ទិដ្ឋភាពប្រតិទិន"
                  >
                    <i class="fas fa-calendar-alt mr-1"></i> ប្រតិទិន
                  </button>
                  <button
                    class="btn"
                    :class="viewMode === 'table' ? 'btn-primary' : 'btn-outline-secondary'"
                    @click="viewMode = 'table'"
                    title="ទិដ្ឋភាពតារាង"
                  >
                    <i class="fas fa-list mr-1"></i> តារាង
                  </button>
                </div>

                <!-- Add Button -->
                <button class="btn btn-sm btn-success shadow-sm mb-2 mb-sm-0" @click="openCreateModal()">
                  <i class="fas fa-plus-circle mr-1"></i> បង្កើតថ្មី
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Calendar View -->
        <div v-if="viewMode === 'calendar'" class="card shadow-sm border-0 rounded-lg">
          <div class="card-body p-2 p-md-3">
            <!-- Weekday Headers -->
            <div class="calendar-grid-header text-center font-weight-bold">
              <div class="calendar-header-cell text-primary">ច័ន្ទ (Mon)</div>
              <div class="calendar-header-cell text-primary">អង្គារ (Tue)</div>
              <div class="calendar-header-cell text-primary">ពុធ (Wed)</div>
              <div class="calendar-header-cell text-primary">ព្រហ (Thu)</div>
              <div class="calendar-header-cell text-primary">សុក្រ (Fri)</div>
              <div class="calendar-header-cell text-danger">សៅរ៍ (Sat)</div>
              <div class="calendar-header-cell text-danger">អាទិត្យ (Sun)</div>
            </div>

            <!-- Calendar Days Grid -->
            <div class="calendar-grid-body">
              <div
                v-for="day in calendarDays"
                :key="day.dateString"
                class="calendar-day-cell"
                :class="{
                  'other-month': !day.isCurrentMonth,
                  'is-today': day.isToday,
                  'is-weekend': day.isWeekend,
                  'has-events': day.events.length > 0
                }"
                @click="onDayClick(day)"
              >
                <!-- Day Number & Add Action -->
                <div class="day-cell-top d-flex justify-content-between align-items-center">
                  <span
                    class="day-number"
                    :class="{ 'badge-today': day.isToday }"
                  >
                    {{ toKhmerNum(day.dayNumber) }}
                  </span>
                  <button
                    class="btn btn-xs btn-link p-0 text-muted quick-add-btn"
                    @click.stop="openCreateModal(day.dateString)"
                    title="បន្ថែមកម្មវិធីលើថ្ងៃនេះ"
                  >
                    <i class="fas fa-plus-circle"></i>
                  </button>
                </div>

                <!-- Events inside this day -->
                <div class="day-events-container">
                  <div
                    v-for="evt in day.events.slice(0, 3)"
                    :key="evt.id"
                    class="event-chip"
                    :style="{
                      borderLeftColor: evt.color || '#3b82f6',
                      backgroundColor: hexToRgba(evt.color || '#3b82f6', 0.12)
                    }"
                    :title="evt.title + ' (' + formatTimeDisplay(evt) + ')'"
                    @click.stop="openDetailModal(evt)"
                  >
                    <span class="event-time" v-if="!evt.all_day">{{ formatShortTime(evt.start_datetime) }}</span>
                    <span class="event-time" v-else><i class="fas fa-sun text-warning mr-1"></i>ពេញថ្ងៃ</span>
                    <span class="event-title text-truncate">
                      <i v-if="evt.meeting_link" class="fas fa-video text-primary mr-1" title="Online Meeting"></i>
                      <i v-else-if="evt.type === 'meeting'" class="fas fa-users mr-1"></i>
                      <i v-else-if="evt.type === 'mission'" class="fas fa-briefcase mr-1"></i>
                      {{ evt.title }}
                    </span>
                  </div>

                  <!-- More events pill -->
                  <div
                    v-if="day.events.length > 3"
                    class="event-more-badge"
                    @click.stop="openDayEventsModal(day)"
                  >
                    +{{ day.events.length - 3 }} ផ្សេងទៀត
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 4. Table / List View -->
        <div v-else class="card shadow-sm border-0 rounded-lg">
          <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title font-weight-bold text-dark mb-0">
              <i class="fas fa-list text-primary mr-2"></i>
              បញ្ជីកាលវិភាគសម្រាប់ {{ formatKhmerMonthYear(currentYear, currentMonth) }}
              <span class="badge badge-secondary ml-2 font-khmer">{{ filteredSchedules.length }} កម្មវិធី</span>
            </h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-striped align-middle mb-0">
                <thead class="thead-light">
                  <tr>
                    <th style="width: 50px;" class="text-center">ល.រ</th>
                    <th style="width: 170px;">កាលបរិច្ឆេទ & ម៉ោង</th>
                    <th>កម្មវិធី / កិច្ចប្រជុំ</th>
                    <th style="width: 130px;">ប្រភេទ</th>
                    <th style="width: 180px;">ទីកន្លែង / តំណភ្ជាប់</th>
                    <th style="width: 110px;">អាទិភាព</th>
                    <th style="width: 130px;">ស្ថានភាព</th>
                    <th style="width: 130px;" class="text-center">សកម្មភាព</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loadingSchedules">
                    <td colspan="8" class="text-center py-5 text-muted">
                      <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>
                      កំពុងផ្ទុកទិន្នន័យកាលវិភាគ...
                    </td>
                  </tr>
                  <tr v-else-if="filteredSchedules.length === 0">
                    <td colspan="8" class="text-center py-5 text-muted">
                      <i class="fas fa-calendar-times fa-3x text-muted mb-2"></i>
                      <p class="mb-0">មិនមានកាលវិភាគ ឬកិច្ចប្រជុំក្នុងខែនេះទេ</p>
                      <button class="btn btn-sm btn-outline-primary mt-2" @click="openCreateModal()">
                        <i class="fas fa-plus mr-1"></i> ចុចទីនេះដើម្បីបង្កើតកាលវិភាគថ្មី
                      </button>
                    </td>
                  </tr>
                  <tr v-for="(item, index) in filteredSchedules" :key="item.id">
                    <td class="text-center font-weight-bold text-muted">{{ index + 1 }}</td>
                    <td>
                      <div class="font-weight-bold text-dark">
                        <i class="far fa-calendar-alt text-primary mr-1"></i>
                        {{ formatKhmerDate(item.start_datetime) }}
                      </div>
                      <small class="text-muted" v-if="!item.all_day">
                        <i class="far fa-clock text-secondary mr-1"></i>
                        {{ formatShortTime(item.start_datetime) }} - {{ formatShortTime(item.end_datetime) }}
                      </small>
                      <span v-else class="badge badge-warning text-dark px-1">
                        <i class="fas fa-sun mr-1"></i>ពេញមួយថ្ងៃ
                      </span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <span
                          class="schedule-color-dot mr-2 flex-shrink-0"
                          :style="{ backgroundColor: item.color || '#3b82f6' }"
                        ></span>
                        <div>
                          <a href="javascript:void(0)" class="font-weight-bold text-dark text-decoration-none" @click="openDetailModal(item)">
                            {{ item.title }}
                          </a>
                          <div v-if="item.description" class="small text-muted text-truncate" style="max-width: 320px;">
                            {{ item.description }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge" :class="getTypeBadgeClass(item.type)">
                        {{ getTypeLabel(item.type) }}
                      </span>
                    </td>
                    <td>
                      <div v-if="item.meeting_link" class="mb-1">
                        <a
                          :href="item.meeting_link"
                          target="_blank"
                          class="btn btn-xs btn-outline-primary px-2"
                          title="ចូលរួមប្រជុំ Online"
                        >
                          <i class="fas fa-video mr-1"></i> ចូលរួមប្រជុំ Online
                        </a>
                      </div>
                      <div v-if="item.venue" class="small text-muted">
                        <i class="fas fa-map-marker-alt text-danger mr-1"></i>
                        {{ item.venue }}
                      </div>
                      <div v-if="!item.venue && !item.meeting_link" class="text-muted small">
                        -
                      </div>
                    </td>
                    <td>
                      <span class="badge" :class="getPriorityBadgeClass(item.priority)">
                        {{ getPriorityLabel(item.priority) }}
                      </span>
                    </td>
                    <td>
                      <div class="dropdown">
                        <button
                          class="btn btn-xs dropdown-toggle"
                          :class="getStatusBtnClass(item.status)"
                          type="button"
                          data-toggle="dropdown"
                          aria-haspopup="true"
                          aria-expanded="false"
                        >
                          {{ getStatusLabel(item.status) }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right">
                          <a class="dropdown-item" href="javascript:void(0)" @click="quickUpdateStatus(item, 'scheduled')">
                            <i class="fas fa-clock text-info mr-2"></i> គ្រោងទុក
                          </a>
                          <a class="dropdown-item" href="javascript:void(0)" @click="quickUpdateStatus(item, 'in_progress')">
                            <i class="fas fa-spinner text-primary mr-2"></i> កំពុងដំណើរការ
                          </a>
                          <a class="dropdown-item" href="javascript:void(0)" @click="quickUpdateStatus(item, 'completed')">
                            <i class="fas fa-check-circle text-success mr-2"></i> បានបញ្ចប់
                          </a>
                          <a class="dropdown-item" href="javascript:void(0)" @click="quickUpdateStatus(item, 'cancelled')">
                            <i class="fas fa-ban text-danger mr-2"></i> បានលុបចោល
                          </a>
                        </div>
                      </div>
                    </td>
                    <td class="text-center">
                      <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" @click="openDetailModal(item)" title="មើលលម្អិត">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-primary" @click="openEditModal(item)" title="កែសម្រួល">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" @click="confirmDelete(item)" title="លុប">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- ============================================================= -->
    <!-- 5. Modal: Create / Edit Schedule -->
    <!-- ============================================================= -->
    <div
      v-if="showScheduleModal"
      class="custom-modal-backdrop"
      @click.self="closeScheduleModal"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 800px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow">
          <!-- Modal Header -->
          <div class="modal-header bg-light py-3">
            <h5 class="modal-title font-weight-bold text-dark">
              <i :class="isEditing ? 'fas fa-edit text-warning' : 'fas fa-plus-circle text-success'" class="mr-2"></i>
              {{ isEditing ? 'កែសម្រួលកាលវិភាគ / កិច្ចប្រជុំ' : 'បង្កើតកាលវិភាគថ្មី' }}
            </h5>
            <button type="button" class="close" @click="closeScheduleModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <!-- Modal Body (Scrollable) -->
          <div class="modal-body flex-grow-1 overflow-auto p-4">
            <form @submit.prevent="saveSchedule">
              <!-- Title -->
              <div class="form-group">
                <label class="font-weight-bold">
                  ចំណងជើងកម្មវិធី / កិច្ចប្រជុំ <span class="text-danger">*</span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  v-model="form.title"
                  placeholder="ឧទាហរណ៍៖ ប្រជុំប្រចាំខែស្តីពីការវាយតម្លៃលទ្ធផលការងារ..."
                  required
                />
              </div>

              <!-- Type, Priority & Color Row -->
              <div class="row">
                <div class="col-md-4 form-group">
                  <label class="font-weight-bold">ប្រភេទកម្មវិធី</label>
                  <select class="custom-select" v-model="form.type">
                    <option value="meeting">កិច្ចប្រជុំ (Meeting)</option>
                    <option value="mission">បេសកកម្ម (Mission)</option>
                    <option value="workshop">សិក្ខាសាលា / វគ្គបណ្តុះបណ្តាល</option>
                    <option value="task">កិច្ចការងារទូទៅ (Task)</option>
                    <option value="appointment">ការណាត់ជួប (Appointment)</option>
                    <option value="other">ផ្សេងៗ (Other)</option>
                  </select>
                </div>

                <div class="col-md-4 form-group">
                  <label class="font-weight-bold">កម្រិតអាទិភាព</label>
                  <select class="custom-select" v-model="form.priority">
                    <option value="low">ទាប (Low)</option>
                    <option value="medium">មធ្យម (Medium)</option>
                    <option value="high">ខ្ពស់ (High)</option>
                    <option value="urgent">បន្ទាន់បំផុត (Urgent)</option>
                  </select>
                </div>

                <div class="col-md-4 form-group">
                  <label class="font-weight-bold">ពណ៌សម្គាល់</label>
                  <div class="d-flex align-items-center mt-1">
                    <div
                      v-for="color in presetColors"
                      :key="color"
                      class="color-swatch mr-2"
                      :style="{ backgroundColor: color }"
                      :class="{ 'active': form.color === color }"
                      @click="form.color = color"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- All day toggle -->
              <div class="form-group">
                <div class="custom-control custom-checkbox">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="allDayCheck"
                    v-model="form.all_day"
                  />
                  <label class="custom-control-label font-weight-bold" for="allDayCheck">
                    ពេញមួយថ្ងៃ (All Day)
                  </label>
                </div>
              </div>

              <!-- Datetime pickers -->
              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="font-weight-bold">
                    កាលបរិច្ឆេទ & ម៉ោងចាប់ផ្តើម <span class="text-danger">*</span>
                  </label>
                  <input
                    :type="form.all_day ? 'date' : 'datetime-local'"
                    class="form-control"
                    v-model="form.start_datetime"
                    required
                  />
                </div>

                <div class="col-md-6 form-group">
                  <label class="font-weight-bold">
                    កាលបរិច្ឆេទ & ម៉ោងបញ្ចប់
                  </label>
                  <input
                    :type="form.all_day ? 'date' : 'datetime-local'"
                    class="form-control"
                    v-model="form.end_datetime"
                  />
                </div>
              </div>

              <!-- Venue & Online Meeting Link -->
              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="font-weight-bold">
                    <i class="fas fa-map-marker-alt text-danger mr-1"></i> ទីកន្លែង / បន្ទប់ប្រជុំ
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="form.venue"
                    placeholder="ឧទាហរណ៍៖ បន្ទប់ប្រជុំធំ ជាន់ទី២..."
                  />
                </div>

                <div class="col-md-6 form-group">
                  <label class="font-weight-bold">
                    <i class="fas fa-video text-primary mr-1"></i> តំណភ្ជាប់ប្រជុំ Online (Link)
                  </label>
                  <input
                    type="url"
                    class="form-control"
                    v-model="form.meeting_link"
                    placeholder="https://zoom.us/j/... ឬ Google Meet Link"
                  />
                </div>
              </div>

              <!-- Status -->
              <div class="form-group" v-if="isEditing">
                <label class="font-weight-bold">ស្ថានភាពកាលវិភាគ</label>
                <select class="custom-select" v-model="form.status">
                  <option value="scheduled">គ្រោងទុក (Scheduled)</option>
                  <option value="in_progress">កំពុងដំណើរការ (In Progress)</option>
                  <option value="completed">បានបញ្ចប់រួចរាល់ (Completed)</option>
                  <option value="cancelled">បានលុបចោល (Cancelled)</option>
                </select>
              </div>

              <!-- Description / Agenda -->
              <div class="form-group mb-0">
                <label class="font-weight-bold">
                  <i class="fas fa-align-left text-muted mr-1"></i> របៀបវារៈ / កំណត់សម្គាល់លម្អិត
                </label>
                <textarea
                  class="form-control"
                  rows="4"
                  v-model="form.description"
                  placeholder="សរសេររបៀបវារៈប្រជុំ ឬព័ត៌មានបន្ថែមផ្សេងៗនៅទីនេះ..."
                ></textarea>
              </div>
            </form>
          </div>

          <!-- Modal Footer (Sticky) -->
          <div class="modal-footer sticky-modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-secondary px-3" @click="closeScheduleModal">
              <i class="fas fa-times mr-1"></i> បោះបង់
            </button>
            <button
              type="button"
              class="btn btn-primary px-4"
              :disabled="saving"
              @click="saveSchedule"
            >
              <span v-if="saving" class="spinner-border spinner-border-sm mr-1" role="status"></span>
              <i v-else class="fas fa-save mr-1"></i>
              {{ isEditing ? 'រក្សាទុកការកែប្រែ' : 'បង្កើតកាលវិភាគ' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- 6. Modal: Schedule Details View -->
    <!-- ============================================================= -->
    <div
      v-if="showDetailModal"
      class="custom-modal-backdrop"
      @click.self="closeDetailModal"
    >
      <div class="modal-dialog modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 600px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow" v-if="selectedSchedule">
          <!-- Detail Header -->
          <div
            class="modal-header py-3 text-white"
            :style="{ backgroundColor: selectedSchedule.color || '#3b82f6' }"
          >
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-calendar-check mr-2"></i> ព័ត៌មានលម្អិតកាលវិភាគ
            </h5>
            <button type="button" class="close text-white" @click="closeDetailModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <!-- Detail Body -->
          <div class="modal-body flex-grow-1 overflow-auto p-4">
            <!-- Badges Bar -->
            <div class="d-flex flex-wrap gap-2 mb-3">
              <span class="badge mr-2 px-2 py-1" :class="getTypeBadgeClass(selectedSchedule.type)">
                {{ getTypeLabel(selectedSchedule.type) }}
              </span>
              <span class="badge mr-2 px-2 py-1" :class="getPriorityBadgeClass(selectedSchedule.priority)">
                អាទិភាព៖ {{ getPriorityLabel(selectedSchedule.priority) }}
              </span>
              <span class="badge px-2 py-1" :class="getStatusBadgeClass(selectedSchedule.status)">
                ស្ថានភាព៖ {{ getStatusLabel(selectedSchedule.status) }}
              </span>
            </div>

            <!-- Title -->
            <h4 class="font-weight-bold text-dark mb-3">
              {{ selectedSchedule.title }}
            </h4>

            <!-- Date & Time Box -->
            <div class="card bg-light border-0 mb-3">
              <div class="card-body p-3">
                <div class="d-flex align-items-center mb-1">
                  <i class="fas fa-calendar-day text-primary fa-lg mr-2" style="width: 24px;"></i>
                  <span class="font-weight-bold text-dark">
                    {{ formatKhmerDate(selectedSchedule.start_datetime) }}
                  </span>
                </div>
                <div class="d-flex align-items-center">
                  <i class="fas fa-clock text-secondary fa-lg mr-2" style="width: 24px;"></i>
                  <span v-if="selectedSchedule.all_day" class="text-warning font-weight-bold">
                    ពេញមួយថ្ងៃ (All Day)
                  </span>
                  <span v-else class="text-muted">
                    {{ formatShortTime(selectedSchedule.start_datetime) }} ដល់ {{ formatShortTime(selectedSchedule.end_datetime) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Online Meeting Link Button -->
            <div v-if="selectedSchedule.meeting_link" class="mb-3">
              <a
                :href="selectedSchedule.meeting_link"
                target="_blank"
                class="btn btn-primary btn-block shadow-sm font-weight-bold py-2"
              >
                <i class="fas fa-video mr-2"></i> ចុចចូលរួមប្រជុំ Online ឥឡូវនេះ
              </a>
              <small class="text-muted text-break mt-1 d-block">
                តំណភ្ជាប់៖ {{ selectedSchedule.meeting_link }}
              </small>
            </div>

            <!-- Venue -->
            <div v-if="selectedSchedule.venue" class="mb-3 d-flex align-items-start">
              <i class="fas fa-map-marker-alt text-danger fa-lg mr-2 mt-1" style="width: 24px;"></i>
              <div>
                <span class="font-weight-bold text-dark">ទីកន្លែង / បន្ទប់ប្រជុំ៖</span>
                <p class="text-muted mb-0">{{ selectedSchedule.venue }}</p>
              </div>
            </div>

            <!-- Description / Agenda -->
            <div v-if="selectedSchedule.description" class="mb-3">
              <h6 class="font-weight-bold text-dark mb-2">
                <i class="fas fa-align-left text-primary mr-1"></i> របៀបវារៈ / កំណត់សម្គាល់៖
              </h6>
              <div class="p-3 bg-light rounded text-dark" style="white-space: pre-line; line-height: 1.6;">
                {{ selectedSchedule.description }}
              </div>
            </div>

            <!-- Created By User Info -->
            <div v-if="selectedSchedule.user" class="border-top pt-2 text-muted small">
              <i class="fas fa-user mr-1"></i> បង្កើតឡើងដោយ៖ <strong>{{ selectedSchedule.user.name_kh || selectedSchedule.user.name }}</strong>
            </div>
          </div>

          <!-- Detail Footer (Sticky) -->
          <div class="modal-footer sticky-modal-footer d-flex justify-content-between">
            <button class="btn btn-outline-danger btn-sm" @click="confirmDelete(selectedSchedule)">
              <i class="fas fa-trash-alt mr-1"></i> លុប
            </button>
            <div>
              <button class="btn btn-outline-secondary btn-sm mr-2" @click="closeDetailModal">
                បិទ
              </button>
              <button class="btn btn-primary btn-sm" @click="openEditFromDetail">
                <i class="fas fa-edit mr-1"></i> កែសម្រួល
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- 7. Modal: All Events of a Day -->
    <!-- ============================================================= -->
    <div
      v-if="showDayEventsModal"
      class="custom-modal-backdrop"
      @click.self="closeDayEventsModal"
    >
      <div class="modal-dialog modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 600px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow" v-if="selectedDay">
          <div class="modal-header bg-light py-3">
            <h5 class="modal-title font-weight-bold text-dark">
              <i class="fas fa-calendar-day text-primary mr-2"></i>
              កាលវិភាគថ្ងៃទី {{ toKhmerNum(selectedDay.dayNumber) }} {{ formatKhmerMonthYear(currentYear, currentMonth) }}
            </h5>
            <button type="button" class="close" @click="closeDayEventsModal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body flex-grow-1 overflow-auto p-3">
            <div v-for="evt in selectedDay.events" :key="evt.id" class="card shadow-sm border mb-2">
              <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="badge" :class="getTypeBadgeClass(evt.type)">
                    {{ getTypeLabel(evt.type) }}
                  </span>
                  <small class="text-muted">
                    <span v-if="evt.all_day" class="badge badge-warning text-dark">ពេញមួយថ្ងៃ</span>
                    <span v-else>{{ formatShortTime(evt.start_datetime) }} - {{ formatShortTime(evt.end_datetime) }}</span>
                  </small>
                </div>
                <h6 class="font-weight-bold text-dark mb-1">{{ evt.title }}</h6>
                <div v-if="evt.venue" class="small text-muted mb-2">
                  <i class="fas fa-map-marker-alt text-danger mr-1"></i> {{ evt.venue }}
                </div>
                <div class="d-flex justify-content-end gap-2">
                  <a v-if="evt.meeting_link" :href="evt.meeting_link" target="_blank" class="btn btn-xs btn-outline-primary mr-1">
                    <i class="fas fa-video mr-1"></i> Meeting Link
                  </a>
                  <button class="btn btn-xs btn-info" @click="openDetailFromDayModal(evt)">
                    <i class="fas fa-eye mr-1"></i> មើលលម្អិត
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer sticky-modal-footer d-flex justify-content-between">
            <button class="btn btn-sm btn-success" @click="openCreateModal(selectedDay.dateString)">
              <i class="fas fa-plus mr-1"></i> បន្ថែមកម្មវិធីលើថ្ងៃនេះ
            </button>
            <button class="btn btn-sm btn-secondary" @click="closeDayEventsModal">
              បិទ
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';
import {
  apiGetWorkSchedules,
  apiGetWorkScheduleSummary,
  apiCreateWorkSchedule,
  apiUpdateWorkSchedule,
  apiUpdateWorkScheduleStatus,
  apiDeleteWorkSchedule
} from '@/functions/api/workSchedule';
import { useUserStore } from '@/stores/user';

const userStore = useUserStore();

// View Mode: 'calendar' or 'table'
const viewMode = ref('calendar');

// Current Date State
const today = new Date();
const currentYear = ref(today.getFullYear());
const currentMonth = ref(today.getMonth() + 1); // 1-12

// Filters & State
const searchKeyword = ref('');
const filterType = ref('');
const filterStatus = ref('');
const schedules = ref([]);
const loadingSchedules = ref(false);

// Summary Stats
const summaryStats = ref({
  total: 0,
  meetings: 0,
  missions: 0,
  completed: 0
});

// Modal & Form State
const isEditing = ref(false);
const editingId = ref(null);
const saving = ref(false);
const selectedSchedule = ref(null);
const selectedDay = ref(null);

const showScheduleModal = ref(false);
const showDetailModal = ref(false);
const showDayEventsModal = ref(false);

const presetColors = [
  '#3b82f6', // Blue
  '#10b981', // Green
  '#f59e0b', // Amber
  '#ef4444', // Red
  '#8b5cf6', // Purple
  '#06b6d4', // Cyan
  '#64748b'  // Slate
];

const form = ref({
  title: '',
  type: 'meeting',
  start_datetime: '',
  end_datetime: '',
  all_day: false,
  venue: '',
  meeting_link: '',
  description: '',
  priority: 'medium',
  status: 'scheduled',
  color: '#3b82f6'
});

// Khmer Month Names
const khmerMonths = [
  '', 'មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា',
  'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'
];

// Khmer Numerals Helper
const toKhmerNum = (num) => {
  if (num === null || num === undefined) return '';
  const str = String(num);
  const khmerDigits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
  return str.replace(/[0-9]/g, (d) => khmerDigits[parseInt(d, 10)]);
};

// Format Month Year in Khmer
const formatKhmerMonthYear = (year, month) => {
  return `ខែ${khmerMonths[month]} ឆ្នាំ${toKhmerNum(year)}`;
};

// Format Date in Khmer e.g. "ថ្ងៃទី ១០ ខែកញ្ញា ឆ្នាំ២០២៦"
const formatKhmerDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  const day = d.getDate();
  const month = d.getMonth() + 1;
  const year = d.getFullYear();
  return `ថ្ងៃទី${toKhmerNum(day)} ខែ${khmerMonths[month]} ឆ្នាំ${toKhmerNum(year)}`;
};

// Short Time format e.g. "09:30 AM"
const formatShortTime = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return '';
  let hours = d.getHours();
  const minutes = String(d.getMinutes()).padStart(2, '0');
  const ampm = hours >= 12 ? 'PM' : 'AM';
  hours = hours % 12;
  hours = hours ? hours : 12;
  return `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
};

const formatTimeDisplay = (evt) => {
  if (evt.all_day) return 'ពេញមួយថ្ងៃ';
  return `${formatShortTime(evt.start_datetime)} - ${formatShortTime(evt.end_datetime)}`;
};

const hexToRgba = (hex, opacity) => {
  if (!hex || hex.length < 6) return `rgba(59, 130, 246, ${opacity})`;
  let c = hex.replace('#', '');
  if (c.length === 3) {
    c = c.split('').map(x => x + x).join('');
  }
  const r = parseInt(c.substring(0, 2), 16) || 0;
  const g = parseInt(c.substring(2, 4), 16) || 0;
  const b = parseInt(c.substring(4, 6), 16) || 0;
  return `rgba(${r}, ${g}, ${b}, ${opacity})`;
};

// Fetch Schedules from Backend
const fetchSchedules = async () => {
  loadingSchedules.value = true;
  try {
    const params = {
      year: currentYear.value,
      month: currentMonth.value
    };
    if (filterType.value) params.type = filterType.value;
    if (filterStatus.value) params.status = filterStatus.value;
    if (searchKeyword.value.trim()) params.search = searchKeyword.value.trim();

    const [listRes, summaryRes] = await Promise.all([
      apiGetWorkSchedules(params),
      apiGetWorkScheduleSummary({ year: currentYear.value, month: currentMonth.value })
    ]);

    schedules.value = listRes.data.data || listRes.data || [];
    if (summaryRes.data && summaryRes.data.data) {
      summaryStats.value = summaryRes.data.data;
    }
  } catch (error) {
    console.error('Failed to fetch work schedules:', error);
  } finally {
    loadingSchedules.value = false;
  }
};

// Filtered schedules for table view
const filteredSchedules = computed(() => {
  return schedules.value;
});

// Month Navigation
const prevMonth = () => {
  if (currentMonth.value === 1) {
    currentMonth.value = 12;
    currentYear.value -= 1;
  } else {
    currentMonth.value -= 1;
  }
  fetchSchedules();
};

const nextMonth = () => {
  if (currentMonth.value === 12) {
    currentMonth.value = 1;
    currentYear.value += 1;
  } else {
    currentMonth.value += 1;
  }
  fetchSchedules();
};

const goToToday = () => {
  const d = new Date();
  currentYear.value = d.getFullYear();
  currentMonth.value = d.getMonth() + 1;
  fetchSchedules();
};

// Compute Calendar Days Grid (Monday through Sunday)
const calendarDays = computed(() => {
  const year = currentYear.value;
  const month = currentMonth.value; // 1-12

  // First day of current month
  const firstDay = new Date(year, month - 1, 1);
  // Total days in current month
  const daysInMonth = new Date(year, month, 0).getDate();

  // Day of week: 0 = Sun, 1 = Mon ... 6 = Sat
  // We want Monday as index 0, Sunday as index 6
  let firstDayOfWeek = firstDay.getDay(); // 0 is Sunday
  let prefixDaysCount = firstDayOfWeek === 0 ? 6 : firstDayOfWeek - 1;

  // Days in previous month
  const daysInPrevMonth = new Date(year, month - 1, 0).getDate();

  const days = [];

  // 1. Previous month trailing days
  for (let i = prefixDaysCount - 1; i >= 0; i--) {
    const dayNum = daysInPrevMonth - i;
    const prevMonthNum = month === 1 ? 12 : month - 1;
    const prevYearNum = month === 1 ? year - 1 : year;
    const dateStr = `${prevYearNum}-${String(prevMonthNum).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
    
    days.push({
      dateString: dateStr,
      dayNumber: dayNum,
      isCurrentMonth: false,
      isToday: false,
      isWeekend: false,
      events: []
    });
  }

  // 2. Current month days
  const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const curDate = new Date(year, month - 1, d);
    const dayOfWeek = curDate.getDay();
    const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;

    // Filter events for this specific day
    const dayEvents = schedules.value.filter(item => {
      if (!item.start_datetime) return false;
      const itemDateStr = item.start_datetime.substring(0, 10);
      return itemDateStr === dateStr;
    });

    days.push({
      dateString: dateStr,
      dayNumber: d,
      isCurrentMonth: true,
      isToday: dateStr === todayStr,
      isWeekend: isWeekend,
      events: dayEvents
    });
  }

  // 3. Next month leading days to complete grid (multiples of 7)
  const remainingCells = (7 - (days.length % 7)) % 7;
  for (let n = 1; n <= remainingCells; n++) {
    const nextMonthNum = month === 12 ? 1 : month + 1;
    const nextYearNum = month === 12 ? year + 1 : year;
    const dateStr = `${nextYearNum}-${String(nextMonthNum).padStart(2, '0')}-${String(n).padStart(2, '0')}`;

    days.push({
      dateString: dateStr,
      dayNumber: n,
      isCurrentMonth: false,
      isToday: false,
      isWeekend: false,
      events: []
    });
  }

  return days;
});

// Labels & Badges
const getTypeLabel = (type) => {
  const map = {
    meeting: 'កិច្ចប្រជុំ',
    mission: 'បេសកកម្ម',
    workshop: 'សិក្ខាសាលា',
    task: 'កិច្ចការងារ',
    appointment: 'ការណាត់ជួប',
    other: 'ផ្សេងៗ'
  };
  return map[type] || 'ទូទៅ';
};

const getTypeBadgeClass = (type) => {
  const map = {
    meeting: 'badge-primary',
    mission: 'badge-warning text-dark',
    workshop: 'badge-info',
    task: 'badge-secondary',
    appointment: 'badge-purple text-white',
    other: 'badge-light'
  };
  return map[type] || 'badge-secondary';
};

const getPriorityLabel = (priority) => {
  const map = {
    low: 'ទាប',
    medium: 'មធ្យម',
    high: 'ខ្ពស់',
    urgent: 'បន្ទាន់'
  };
  return map[priority] || 'មធ្យម';
};

const getPriorityBadgeClass = (priority) => {
  const map = {
    low: 'badge-light text-muted border',
    medium: 'badge-info',
    high: 'badge-warning text-dark',
    urgent: 'badge-danger'
  };
  return map[priority] || 'badge-info';
};

const getStatusLabel = (status) => {
  const map = {
    scheduled: 'គ្រោងទុក',
    in_progress: 'កំពុងដំណើរការ',
    completed: 'បានបញ្ចប់',
    cancelled: 'បានលុបចោល'
  };
  return map[status] || status;
};

const getStatusBadgeClass = (status) => {
  const map = {
    scheduled: 'badge-info',
    in_progress: 'badge-primary',
    completed: 'badge-success',
    cancelled: 'badge-danger'
  };
  return map[status] || 'badge-secondary';
};

const getStatusBtnClass = (status) => {
  const map = {
    scheduled: 'btn-outline-info',
    in_progress: 'btn-outline-primary',
    completed: 'btn-outline-success',
    cancelled: 'btn-outline-danger'
  };
  return map[status] || 'btn-outline-secondary';
};

// Modal Actions
const openCreateModal = (prefillDate = null) => {
  isEditing.value = false;
  editingId.value = null;

  let defaultStart = '';
  let defaultEnd = '';

  if (prefillDate) {
    defaultStart = `${prefillDate}T09:00`;
    defaultEnd = `${prefillDate}T10:00`;
  } else {
    const now = new Date();
    const datePart = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    defaultStart = `${datePart}T09:00`;
    defaultEnd = `${datePart}T10:00`;
  }

  form.value = {
    title: '',
    type: 'meeting',
    start_datetime: defaultStart,
    end_datetime: defaultEnd,
    all_day: false,
    venue: '',
    meeting_link: '',
    description: '',
    priority: 'medium',
    status: 'scheduled',
    color: presetColors[0]
  };

  showDayEventsModal.value = false;
  showDetailModal.value = false;
  showScheduleModal.value = true;
};

const openEditModal = (item) => {
  isEditing.value = true;
  editingId.value = item.id;

  // Format ISO strings to datetime-local format "YYYY-MM-DDTHH:mm"
  const formatForInput = (dtStr) => {
    if (!dtStr) return '';
    const d = new Date(dtStr);
    if (isNaN(d.getTime())) return '';
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
  };

  form.value = {
    title: item.title,
    type: (item.type || 'meeting').toLowerCase(),
    start_datetime: item.all_day ? (item.start_datetime ? item.start_datetime.substring(0, 10) : '') : formatForInput(item.start_datetime),
    end_datetime: item.all_day ? (item.end_datetime ? item.end_datetime.substring(0, 10) : '') : formatForInput(item.end_datetime),
    all_day: !!item.all_day,
    venue: item.venue || item.location || '',
    meeting_link: item.meeting_link || '',
    description: item.description || '',
    priority: (item.priority || 'medium').toLowerCase() === 'normal' ? 'medium' : (item.priority || 'medium').toLowerCase(),
    status: (item.status || 'scheduled').toLowerCase(),
    color: item.color || presetColors[0]
  };

  showDetailModal.value = false;
  showDayEventsModal.value = false;
  showScheduleModal.value = true;
};

const closeScheduleModal = () => {
  showScheduleModal.value = false;
};

// Save Schedule (Create or Update)
const saveSchedule = async () => {
  if (!form.value.title.trim()) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមបំពេញព័ត៌មាន',
      text: 'សូមបញ្ចូលចំណងជើងកម្មវិធី ឬកិច្ចប្រជុំ!',
      confirmButtonText: 'យល់ព្រម'
    });
    return;
  }

  if (!form.value.start_datetime) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមជ្រើសរើសកាលបរិច្ឆេទ',
      text: 'សូមបញ្ចូលកាលបរិច្ឆេទចាប់ផ្តើម!',
      confirmButtonText: 'យល់ព្រម'
    });
    return;
  }

  saving.value = true;
  try {
    const payload = { ...form.value };

    if (isEditing.value) {
      await apiUpdateWorkSchedule(editingId.value, payload);
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ',
        text: 'បានកែសម្រួលកាលវិភាគដោយជោគជ័យ!',
        timer: 1500,
        showConfirmButton: false
      });
    } else {
      await apiCreateWorkSchedule(payload);
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ',
        text: 'បានបង្កើតកាលវិភាគថ្មីដោយជោគជ័យ!',
        timer: 1500,
        showConfirmButton: false
      });
    }

    closeScheduleModal();
    fetchSchedules();
  } catch (error) {
    console.error('Failed to save schedule:', error);
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ',
      text: error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុកកាលវិភាគ!',
      confirmButtonText: 'យល់ព្រម'
    });
  } finally {
    saving.value = false;
  }
};

// Quick Status Update
const quickUpdateStatus = async (item, newStatus) => {
  try {
    await apiUpdateWorkScheduleStatus(item.id, newStatus);
    item.status = newStatus;
    fetchSchedules();
  } catch (error) {
    console.error('Failed to update status:', error);
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ',
      text: 'មិនអាចកែប្រែស្ថានភាពបានទេ!',
      confirmButtonText: 'យល់ព្រម'
    });
  }
};

// Confirm Delete
const confirmDelete = (item) => {
  Swal.fire({
    title: 'តើលោកអ្នកប្រាកដទេ?',
    text: `តើលោកអ្នកពិតជាចង់លុបកាលវិភាគ "${item.title}" នេះមែនទេ?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'បាទ/ចាស លុប',
    cancelButtonText: 'បោះបង់'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await apiDeleteWorkSchedule(item.id);
        Swal.fire({
          icon: 'success',
          title: 'បានលុបរួចរាល់!',
          text: 'កាលវិភាគត្រូវបានលុបចេញដោយជោគជ័យ។',
          timer: 1500,
          showConfirmButton: false
        });
        showDetailModal.value = false;
        fetchSchedules();
      } catch (error) {
        console.error('Failed to delete schedule:', error);
        Swal.fire({
          icon: 'error',
          title: 'បរាជ័យ',
          text: 'មិនអាចលុបកាលវិភាគបានទេ!',
          confirmButtonText: 'យល់ព្រម'
        });
      }
    }
  });
};

// Detail Modal Actions
const openDetailModal = (item) => {
  selectedSchedule.value = item;
  showDayEventsModal.value = false;
  showDetailModal.value = true;
};

const closeDetailModal = () => {
  showDetailModal.value = false;
};

const openEditFromDetail = () => {
  if (selectedSchedule.value) {
    openEditModal(selectedSchedule.value);
  }
};

// Day events click
const onDayClick = (day) => {
  if (day.events.length > 0) {
    openDayEventsModal(day);
  } else {
    openCreateModal(day.dateString);
  }
};

const openDayEventsModal = (day) => {
  selectedDay.value = day;
  showDayEventsModal.value = true;
};

const closeDayEventsModal = () => {
  showDayEventsModal.value = false;
};

const openDetailFromDayModal = (evt) => {
  openDetailModal(evt);
};

onMounted(() => {
  fetchSchedules();
});
</script>

<style scoped>
.font-khmer {
  font-family: 'Kantumruy Pro', 'Siemreap', 'Battambang', sans-serif, -apple-system;
}

/* Calendar Grid Styling */
.calendar-grid-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background-color: #f1f5f9;
  border-radius: 8px 8px 0 0;
  border: 1px solid #e2e8f0;
}

.calendar-header-cell {
  padding: 10px 4px;
  font-size: 0.9rem;
  letter-spacing: 0.3px;
}

.calendar-grid-body {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  background-color: #e2e8f0;
  border: 1px solid #e2e8f0;
  border-top: none;
  border-radius: 0 0 8px 8px;
}

.calendar-day-cell {
  min-height: 120px;
  background-color: #ffffff;
  padding: 6px;
  display: flex;
  flex-direction: column;
  transition: all 0.15s ease-in-out;
  cursor: pointer;
}

.calendar-day-cell:hover {
  background-color: #f8fafc;
}

.calendar-day-cell.other-month {
  background-color: #fafbfc;
  opacity: 0.55;
}

.calendar-day-cell.is-weekend {
  background-color: #fdfdfd;
}

.calendar-day-cell.is-today {
  background-color: #f0fdf4;
}

.day-cell-top {
  margin-bottom: 4px;
}

.day-number {
  font-weight: 700;
  font-size: 0.95rem;
  color: #334155;
  width: 26px;
  height: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
}

.badge-today {
  background-color: #10b981;
  color: #ffffff !important;
}

.quick-add-btn {
  opacity: 0;
  transition: opacity 0.2s ease;
}

.calendar-day-cell:hover .quick-add-btn {
  opacity: 1;
}

/* Event Chips */
.day-events-container {
  display: flex;
  flex-direction: column;
  gap: 3px;
  overflow: hidden;
}

.event-chip {
  font-size: 0.76rem;
  padding: 2px 6px;
  border-radius: 4px;
  border-left-width: 3px;
  border-left-style: solid;
  color: #1e293b;
  display: flex;
  align-items: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
  transition: transform 0.1s ease, filter 0.1s ease;
}

.event-chip:hover {
  transform: translateY(-1px);
  filter: brightness(0.95);
}

.event-time {
  font-weight: 700;
  margin-right: 4px;
  font-size: 0.72rem;
  color: #475569;
}

.event-title {
  flex-grow: 1;
}

.event-more-badge {
  font-size: 0.72rem;
  color: #64748b;
  font-weight: 600;
  padding: 1px 4px;
  border-radius: 3px;
  background-color: #f1f5f9;
  text-align: center;
  margin-top: 2px;
}

.event-more-badge:hover {
  background-color: #e2e8f0;
  color: #1e293b;
}

/* Color Swatches */
.color-swatch {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
  border: 2px solid transparent;
}

.color-swatch:hover {
  transform: scale(1.15);
}

.color-swatch.active {
  border-color: #0f172a;
  box-shadow: 0 0 0 2px #fff;
  transform: scale(1.15);
}

.schedule-color-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
}

.badge-purple {
  background-color: #8b5cf6;
}

/* Custom Modal Backdrop */
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

/* Sticky Modal Layout Fix */
.custom-modal-content {
  max-height: calc(100vh - 60px);
  border-radius: 0.5rem;
}

.sticky-modal-footer {
  position: sticky;
  bottom: 0;
  z-index: 105;
  background-color: #ffffff;
  border-top: 1px solid #dee2e6;
  flex-shrink: 0;
}

@media (max-width: 768px) {
  .calendar-day-cell {
    min-height: 80px;
    padding: 3px;
  }
  .calendar-header-cell {
    font-size: 0.75rem;
    padding: 6px 2px;
  }
  .event-time {
    display: none;
  }
  .event-chip {
    font-size: 0.7rem;
    padding: 1px 3px;
  }
}
</style>

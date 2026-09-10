<template>
  <div class="content-wrapper meeting-room-wrapper" style="min-height: 900px;">
    <!-- Content Header -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0 font-khmer text-dark font-weight-bold">
              <i class="fas mr-2" :class="activeTab === 'management' ? 'fa-tasks text-success' : 'fa-door-open text-primary'"></i>
              {{ activeTab === 'management' ? 'គ្រប់គ្រងបន្ទប់ប្រជុំ & ការចាត់ចែង' : 'បន្ទប់ប្រជុំ & ការកក់បន្ទប់' }}
            </h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right font-khmer">
              <li class="breadcrumb-item">
                <router-link :to="{ name: 'dashboard' }">ទំព័រដើម</router-link>
              </li>
              <li class="breadcrumb-item active">
                {{ activeTab === 'management' ? 'គ្រប់គ្រងបន្ទប់ប្រជុំ' : 'បន្ទប់ប្រជុំ & ការកក់' }}
              </li>
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
                <h3>{{ toKhmerNum(rooms.length) }}</h3>
                <p class="font-weight-bold mb-0">បន្ទប់ប្រជុំសរុប</p>
                <small class="text-white-50">{{ toKhmerNum(activeRoomsCount) }} បន្ទប់កំពុងដំណើរការ</small>
              </div>
              <div class="icon">
                <i class="fas fa-door-closed"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-success shadow-sm rounded-lg">
              <div class="inner">
                <h3>{{ toKhmerNum(approvedCountMonth) }}</h3>
                <p class="font-weight-bold mb-0">ការកក់បានអនុម័ត</p>
                <small class="text-white-50">ក្នុងខែ {{ formatKhmerMonthYear(currentYear, currentMonth) }}</small>
              </div>
              <div class="icon">
                <i class="fas fa-calendar-check"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-warning shadow-sm rounded-lg">
              <div class="inner">
                <h3 class="text-dark">{{ toKhmerNum(pendingApprovalsCount) }}</h3>
                <p class="font-weight-bold mb-0 text-dark">រង់ចាំការពិនិត្យ</p>
                <small class="text-dark-50">ត្រូវការការចាត់ចែងបន្ទប់</small>
              </div>
              <div class="icon">
                <i class="fas fa-clock"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box bg-gradient-primary shadow-sm rounded-lg">
              <div class="inner">
                <h3>{{ toKhmerNum(myBookingsCount) }}</h3>
                <p class="font-weight-bold mb-0">ការកក់របស់ខ្ញុំ</p>
                <small class="text-white-50">កិច្ចប្រជុំដែលបានស្នើសុំ</small>
              </div>
              <div class="icon">
                <i class="fas fa-user-clock"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Navigation Tabs & Quick Action Bar -->
        <div class="card shadow-sm border-0 mb-3 rounded-lg">
          <div class="card-header p-2 bg-white border-bottom d-flex flex-wrap align-items-center justify-content-between">
            <ul class="nav nav-pills font-khmer">
              <li class="nav-item">
                <a
                  class="nav-link cursor-pointer font-weight-bold"
                  :class="{ active: activeTab === 'timetable' }"
                  @click="activeTab = 'timetable'"
                >
                  <i class="fas fa-calendar-alt mr-1"></i> កាលវិភាគបន្ទប់ប្រជុំ
                </a>
              </li>
              <li class="nav-item">
                <a
                  class="nav-link cursor-pointer font-weight-bold"
                  :class="{ active: activeTab === 'rooms' }"
                  @click="activeTab = 'rooms'"
                >
                  <i class="fas fa-building mr-1"></i> បញ្ជីបន្ទប់ប្រជុំ
                  <span class="badge badge-light ml-1">{{ toKhmerNum(rooms.length) }}</span>
                </a>
              </li>
              <li class="nav-item">
                <a
                  class="nav-link cursor-pointer font-weight-bold"
                  :class="{ active: activeTab === 'my-bookings' }"
                  @click="activeTab = 'my-bookings'"
                >
                  <i class="fas fa-list-alt mr-1"></i> ការកក់របស់ខ្ញុំ
                  <span class="badge badge-info ml-1">{{ toKhmerNum(myBookings.length) }}</span>
                </a>
              </li>
              <li class="nav-item" v-if="canManage">
                <a
                  class="nav-link cursor-pointer font-weight-bold text-dark"
                  :class="{ active: activeTab === 'management' }"
                  @click="activeTab = 'management'"
                >
                  <i class="fas fa-tasks text-danger mr-1"></i> គ្រប់គ្រង & អនុម័ត
                  <span v-if="pendingApprovalsCount > 0" class="badge badge-danger ml-1 pulse-badge">
                    {{ toKhmerNum(pendingApprovalsCount) }}
                  </span>
                </a>
              </li>
            </ul>

            <div class="mt-2 mt-md-0">
              <button
                class="btn btn-primary btn-sm shadow-sm font-weight-bold"
                @click="openCreateBookingModal()"
              >
                <i class="fas fa-calendar-plus mr-1"></i> ស្នើសុំកក់បន្ទប់ប្រជុំ
              </button>
            </div>
          </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 1: 📅 TIMETABLE & CALENDAR VIEW -->
        <!-- ============================================================= -->
        <div v-show="activeTab === 'timetable'">
          <!-- Filter & Month Switcher Bar -->
          <div class="card shadow-sm border-0 mb-3 rounded-lg">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <!-- Month Navigator -->
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

                <!-- Filters & View Switcher -->
                <div class="col-xl-8 col-lg-7 col-md-6 d-flex flex-wrap justify-content-md-end align-items-center">
                  <!-- Room Selector Filter -->
                  <select
                    class="custom-select custom-select-sm mr-2 mb-2 mb-sm-0"
                    style="width: 220px;"
                    v-model="filterRoomId"
                    @change="fetchTimetable"
                  >
                    <option value="">🏢 គ្រប់បន្ទប់ទាំងអស់</option>
                    <option v-for="r in rooms" :key="r.id" :value="r.id">
                      {{ r.name }} ({{ toKhmerNum(r.capacity) }} នាក់)
                    </option>
                  </select>

                  <!-- View Switcher -->
                  <div class="btn-group btn-group-sm shadow-sm">
                    <button
                      class="btn"
                      :class="timetableViewMode === 'calendar' ? 'btn-primary' : 'btn-outline-secondary'"
                      @click="timetableViewMode = 'calendar'"
                      title="ទិដ្ឋភាពប្រតិទិន"
                    >
                      <i class="fas fa-calendar-alt mr-1"></i> ប្រតិទិន
                    </button>
                    <button
                      class="btn"
                      :class="timetableViewMode === 'agenda' ? 'btn-primary' : 'btn-outline-secondary'"
                      @click="timetableViewMode = 'agenda'"
                      title="ទិដ្ឋភាពរបៀបវារៈ"
                    >
                      <i class="fas fa-list-ul mr-1"></i> កាលវិភាគលម្អិត
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Calendar Grid View -->
          <div class="card shadow-sm border-0 rounded-lg overflow-hidden" v-if="timetableViewMode === 'calendar'">
            <div class="card-body p-0">
              <!-- Days of week header -->
              <div class="calendar-header-row d-flex text-center font-weight-bold border-bottom bg-light text-secondary">
                <div class="calendar-header-cell flex-fill py-2 text-danger">អាទិត្យ</div>
                <div class="calendar-header-cell flex-fill py-2">ច័ន្ទ</div>
                <div class="calendar-header-cell flex-fill py-2">អង្គារ</div>
                <div class="calendar-header-cell flex-fill py-2">ពុធ</div>
                <div class="calendar-header-cell flex-fill py-2">ព្រហស្បតិ៍</div>
                <div class="calendar-header-cell flex-fill py-2">សុក្រ</div>
                <div class="calendar-header-cell flex-fill py-2 text-info">សៅរ៍</div>
              </div>

              <!-- Loading State -->
              <div v-if="loadingTimetable" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <div class="mt-2 text-muted">កំពុងទាញយកកាលវិភាគបន្ទប់...</div>
              </div>

              <!-- Calendar Month Grid -->
              <div v-else class="calendar-grid">
                <div
                  v-for="(day, index) in calendarDays"
                  :key="index"
                  class="calendar-day-cell border"
                  :class="{
                    'other-month': !day.isCurrentMonth,
                    'today-cell': day.isToday,
                    'weekend-cell': day.isWeekend
                  }"
                >
                  <div class="day-cell-header d-flex justify-content-between align-items-center mb-1">
                    <span
                      class="day-number font-weight-bold"
                      :class="{
                        'today-badge': day.isToday,
                        'text-danger': day.isSunday,
                        'text-muted': !day.isCurrentMonth
                      }"
                    >
                      {{ toKhmerNum(day.dayNumber) }}
                    </span>
                    <button
                      v-if="day.isCurrentMonth"
                      class="btn btn-link btn-xs p-0 text-muted add-day-btn"
                      title="ស្នើសុំកក់លើថ្ងៃនេះ"
                      @click="openCreateBookingModal(day.dateString)"
                    >
                      <i class="fas fa-plus-circle"></i>
                    </button>
                  </div>

                  <!-- Events / Bookings inside Day -->
                  <div class="day-events-container">
                    <div
                      v-for="booking in day.bookings.slice(0, 3)"
                      :key="booking.id"
                      class="event-chip mb-1 p-1 rounded text-truncate cursor-pointer"
                      :style="{
                        backgroundColor: (booking.room?.color || '#3b82f6') + '22',
                        borderLeft: `4px solid ${booking.room?.color || '#3b82f6'}`,
                        color: '#0f172a'
                      }"
                      @click="openBookingDetailModal(booking)"
                      :title="`${booking.subject} (${formatTimeOnly(booking.start_datetime)} - ${formatTimeOnly(booking.end_datetime)})`"
                    >
                      <div class="event-time small font-weight-bold" :style="{ color: booking.room?.color || '#1d4ed8' }">
                        {{ formatTimeOnly(booking.start_datetime) }} - {{ formatTimeOnly(booking.end_datetime) }}
                      </div>
                      <div class="event-title small font-weight-bold text-truncate">
                        {{ booking.subject }}
                      </div>
                      <div class="event-room text-muted text-truncate" style="font-size: 0.72rem;">
                        <i class="fas fa-door-open mr-1"></i>{{ booking.room?.name || 'បន្ទប់ប្រជុំ' }}
                      </div>
                    </div>

                    <!-- More indicator -->
                    <div
                      v-if="day.bookings.length > 3"
                      class="more-events-chip text-center cursor-pointer small font-weight-bold text-primary mt-1"
                      @click="openDayEventsModal(day)"
                    >
                      +{{ toKhmerNum(day.bookings.length - 3) }} កិច្ចប្រជុំទៀត
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Agenda / Table View -->
          <div class="card shadow-sm border-0 rounded-lg" v-else>
            <div class="card-body p-0 table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                  <tr>
                    <th style="width: 140px;">កាលបរិច្ឆេទ</th>
                    <th style="width: 130px;">ពេលវេលា</th>
                    <th style="width: 180px;">បន្ទប់ប្រជុំ</th>
                    <th>ប្រធានបទ & អ្នកដឹកនាំ</th>
                    <th style="width: 140px;">អ្នកស្នើសុំ</th>
                    <th style="width: 80px;" class="text-center">ព័ត៌មាន</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loadingTimetable">
                    <td colspan="6" class="text-center py-4 text-muted">
                      <div class="spinner-border spinner-border-sm text-primary mr-1"></div> កំពុងផ្ទុកទិន្នន័យ...
                    </td>
                  </tr>
                  <tr v-else-if="timetableBookings.length === 0">
                    <td colspan="6" class="text-center py-5 text-muted">
                      <i class="fas fa-calendar-times fa-2x mb-2 d-block text-secondary"></i>
                      មិនមានកិច្ចប្រជុំដែលបានអនុម័តក្នុងខែនេះទេ
                    </td>
                  </tr>
                  <tr v-for="b in timetableBookings" :key="b.id">
                    <td class="font-weight-bold text-dark">
                      {{ formatKhmerDate(b.start_datetime) }}
                    </td>
                    <td>
                      <span class="badge badge-light border text-primary">
                        <i class="far fa-clock mr-1"></i>
                        {{ formatTimeOnly(b.start_datetime) }} - {{ formatTimeOnly(b.end_datetime) }}
                      </span>
                    </td>
                    <td>
                      <span
                        class="badge px-2 py-1 text-white"
                        :style="{ backgroundColor: b.room?.color || '#3b82f6' }"
                      >
                        <i class="fas fa-door-open mr-1"></i> {{ b.room?.name }}
                      </span>
                      <div class="small text-muted mt-1">{{ b.room?.location }}</div>
                    </td>
                    <td>
                      <div class="font-weight-bold text-dark">{{ b.subject }}</div>
                      <div class="small text-muted" v-if="b.leader">
                        <i class="fas fa-user-tie mr-1 text-secondary"></i> ប្រធានអង្គប្រជុំ៖ {{ b.leader }}
                      </div>
                    </td>
                    <td>
                      <div class="small font-weight-bold">{{ b.user?.name_kh || b.user?.name }}</div>
                      <div class="small text-muted">{{ b.user?.department?.name_kh || '' }}</div>
                    </td>
                    <td class="text-center">
                      <button class="btn btn-sm btn-outline-info" @click="openBookingDetailModal(b)" title="មើលព័ត៌មានលម្អិត">
                        <i class="fas fa-eye"></i>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 2: 🏢 MEETING ROOMS DIRECTORY -->
        <!-- ============================================================= -->
        <div v-show="activeTab === 'rooms'">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="font-weight-bold text-dark mb-0">
              <i class="fas fa-door-closed text-primary mr-2"></i> បន្ទប់ប្រជុំដែលមានក្នុងប្រព័ន្ធ
            </h5>
            <button v-if="canManage" class="btn btn-success btn-sm shadow-sm font-weight-bold" @click="openCreateRoomModal">
              <i class="fas fa-plus mr-1"></i> បន្ថែមបន្ទប់ថ្មី
            </button>
          </div>

          <div class="row">
            <div v-if="loadingRooms" class="col-12 text-center py-5">
              <div class="spinner-border text-primary" role="status"></div>
              <div class="mt-2 text-muted">កំពុងទាញយកបញ្ជីបន្ទប់...</div>
            </div>

            <div v-else-if="rooms.length === 0" class="col-12 text-center py-5 bg-white rounded shadow-sm">
              <i class="fas fa-door-closed fa-3x text-muted mb-3"></i>
              <h6 class="text-secondary font-weight-bold">មិនទាន់មានបន្ទប់ប្រជុំក្នុងប្រព័ន្ធនៅឡើយទេ</h6>
              <p class="text-muted small">សូមចុចប៊ូតុង "បន្ថែមបន្ទប់ថ្មី" ខាងលើ ដើម្បីបង្កើតបន្ទប់ប្រជុំដំបូង</p>
            </div>

            <div v-for="room in rooms" :key="room.id" class="col-lg-4 col-md-6 mb-4">
              <div class="card h-100 shadow-sm border-0 room-card rounded-lg overflow-hidden">
                <!-- Top Color Bar -->
                <div class="room-color-bar" :style="{ backgroundColor: room.color || '#3b82f6' }"></div>

                <div class="card-body d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title font-weight-bold text-dark mb-0">
                      {{ room.name }}
                    </h5>
                    <span
                      class="badge"
                      :class="room.status === 'ACTIVE' ? 'badge-success' : 'badge-secondary'"
                    >
                      {{ room.status === 'ACTIVE' ? 'សកម្ម' : 'ផ្អាក / ជួសជុល' }}
                    </span>
                  </div>

                  <div class="text-muted small mb-2">
                    <i class="fas fa-map-marker-alt text-danger mr-1"></i> {{ room.location }}
                  </div>

                  <div class="mb-3">
                    <span class="badge badge-light border text-dark font-weight-bold mr-2">
                      <i class="fas fa-users text-primary mr-1"></i> ចំណុះ {{ toKhmerNum(room.capacity) }} នាក់
                    </span>
                    <span v-if="room.manager" class="badge badge-light border text-muted">
                      <i class="fas fa-user-shield text-info mr-1"></i> អ្នកគ្រប់គ្រង៖ {{ room.manager.name_kh || room.manager.name }}
                    </span>
                  </div>

                  <p class="card-text text-secondary small flex-grow-1 mb-3" v-if="room.description">
                    {{ room.description }}
                  </p>

                  <!-- Facilities list -->
                  <div class="facilities-container mb-3" v-if="room.facilities_list && room.facilities_list.length > 0">
                    <div class="small font-weight-bold text-dark mb-1">
                      <i class="fas fa-tools text-muted mr-1"></i> សម្ភារបរិក្ខារបំពាក់៖
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                      <span
                        v-for="(fac, fIdx) in room.facilities_list"
                        :key="fIdx"
                        class="badge badge-light border text-secondary font-weight-normal mr-1 mb-1"
                      >
                        {{ fac }}
                      </span>
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                    <button
                      class="btn btn-primary btn-sm px-3"
                      :disabled="room.status !== 'ACTIVE'"
                      @click="openCreateBookingModal(null, room.id)"
                    >
                      <i class="fas fa-calendar-plus mr-1"></i> ស្នើសុំកក់បន្ទប់នេះ
                    </button>

                    <div v-if="canManage" class="btn-group btn-group-sm">
                      <button class="btn btn-outline-secondary" @click="openEditRoomModal(room)" title="កែប្រែបន្ទប់">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-outline-danger" @click="confirmDeleteRoom(room)" title="លុបបន្ទប់">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ============================================================= -->
        <!-- TAB 3: 📋 MY BOOKINGS -->
        <!-- ============================================================= -->
        <div v-show="activeTab === 'my-bookings'">
          <div class="card shadow-sm border-0 mb-3 rounded-lg">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <!-- Search -->
                <div class="col-md-4 mb-2 mb-md-0">
                  <div class="input-group input-group-sm">
                    <input
                      type="text"
                      class="form-control"
                      placeholder="ស្វែងរកប្រធានបទ ឬអ្នកដឹកនាំ..."
                      v-model="myBookingsSearch"
                      @keyup.enter="fetchMyBookings"
                    />
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" @click="fetchMyBookings">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Status Filter -->
                <div class="col-md-4 mb-2 mb-md-0">
                  <select
                    class="custom-select custom-select-sm"
                    v-model="myBookingsStatusFilter"
                    @change="fetchMyBookings"
                  >
                    <option value="">គ្រប់ស្ថានភាព</option>
                    <option value="PENDING">រង់ចាំការពិនិត្យ (Pending)</option>
                    <option value="APPROVED">បានអនុម័ត (Approved)</option>
                    <option value="REJECTED">បានបដិសេធ (Rejected)</option>
                    <option value="CANCELLED">បានបោះបង់ (Cancelled)</option>
                  </select>
                </div>

                <!-- Add Button -->
                <div class="col-md-4 text-md-right">
                  <button class="btn btn-success btn-sm font-weight-bold" @click="openCreateBookingModal()">
                    <i class="fas fa-plus-circle mr-1"></i> ស្នើសុំកក់ថ្មី
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- My Bookings Table -->
          <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
            <div class="card-body p-0 table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                  <tr>
                    <th style="width: 140px;">កាលបរិច្ឆេទ</th>
                    <th style="width: 130px;">ម៉ោង</th>
                    <th>ប្រធានបទ & អ្នកដឹកនាំ</th>
                    <th style="width: 200px;">បន្ទប់ប្រជុំ</th>
                    <th style="width: 110px;">អ្នកចូលរួម</th>
                    <th style="width: 140px;">ស្ថានភាព</th>
                    <th style="width: 150px;" class="text-center">សកម្មភាព</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loadingMyBookings">
                    <td colspan="7" class="text-center py-4 text-muted">
                      <div class="spinner-border spinner-border-sm text-primary mr-1"></div> កំពុងផ្ទុកទិន្នន័យ...
                    </td>
                  </tr>
                  <tr v-else-if="myBookings.length === 0">
                    <td colspan="7" class="text-center py-5 text-muted">
                      <i class="fas fa-folder-open fa-2x mb-2 d-block text-secondary"></i>
                      មិនមានប្រវត្តិការស្នើសុំកក់បន្ទប់ទេ
                    </td>
                  </tr>
                  <tr v-for="b in myBookings" :key="b.id">
                    <td class="font-weight-bold text-dark">
                      {{ formatKhmerDate(b.start_datetime) }}
                    </td>
                    <td>
                      <span class="badge badge-light border">
                        {{ formatTimeOnly(b.start_datetime) }} - {{ formatTimeOnly(b.end_datetime) }}
                      </span>
                    </td>
                    <td>
                      <div class="font-weight-bold text-dark">{{ b.subject }}</div>
                      <div class="small text-muted" v-if="b.leader">
                        <i class="fas fa-user-tie mr-1 text-secondary"></i> {{ b.leader }}
                      </div>
                    </td>
                    <td>
                      <div v-if="b.room">
                        <span
                          class="badge px-2 py-1 text-white"
                          :style="{ backgroundColor: b.room.color || '#3b82f6' }"
                        >
                          <i class="fas fa-door-open mr-1"></i> {{ b.room.name }}
                        </span>
                        <div class="small text-muted mt-1">{{ b.room.location }}</div>
                      </div>
                      <div v-else class="text-muted small font-italic">
                        <span class="badge badge-warning text-dark">
                          <i class="fas fa-hourglass-half mr-1"></i> រង់ចាំកំណត់បន្ទប់
                        </span>
                        <div v-if="b.preferred_room" class="mt-1">
                          ចង់បាន៖ {{ b.preferred_room.name }}
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge badge-light border">
                        <i class="fas fa-users mr-1"></i> {{ toKhmerNum(b.participants_count || 0) }} នាក់
                      </span>
                    </td>
                    <td>
                      <span :class="getStatusBadgeClass(b.status)">
                        {{ b.status_khmer || b.status }}
                      </span>
                      <div v-if="b.status === 'REJECTED' && b.rejection_reason" class="small text-danger mt-1 text-truncate" style="max-width: 150px;" :title="b.rejection_reason">
                        មូលហេតុ៖ {{ b.rejection_reason }}
                      </div>
                    </td>
                    <td class="text-center">
                      <div class="btn-group btn-group-sm">
                        <!-- View Detail -->
                        <button class="btn btn-outline-info" @click="openBookingDetailModal(b)" title="មើលព័ត៌មានលម្អិត">
                          <i class="fas fa-eye"></i>
                        </button>

                        <!-- Edit (if PENDING or APPROVED) -->
                        <button
                          v-if="b.status === 'PENDING' || b.status === 'APPROVED'"
                          class="btn btn-outline-primary"
                          @click="openEditBookingModal(b)"
                          title="កែសម្រួលការកក់"
                        >
                          <i class="fas fa-edit"></i>
                        </button>

                        <!-- Cancel (if PENDING or APPROVED) -->
                        <button
                          v-if="b.status === 'PENDING' || b.status === 'APPROVED'"
                          class="btn btn-outline-warning text-dark"
                          @click="confirmCancelBooking(b)"
                          title="បោះបង់ការកក់"
                        >
                          <i class="fas fa-ban"></i>
                        </button>

                        <!-- Delete (if PENDING, CANCELLED or REJECTED) -->
                        <button
                          v-if="b.status !== 'APPROVED'"
                          class="btn btn-outline-danger"
                          @click="confirmDeleteBooking(b)"
                          title="លុបចោល"
                        >
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

        <!-- ============================================================= -->
        <!-- TAB 4: 🛡️ MANAGEMENT & APPROVALS (Admin / Room Managers) -->
        <!-- ============================================================= -->
        <div v-show="activeTab === 'management'" v-if="canManage">
          <!-- Management Filter & Search Bar -->
          <div class="card shadow-sm border-0 mb-3 rounded-lg">
            <div class="card-body p-3">
              <div class="row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                  <div class="input-group input-group-sm">
                    <input
                      type="text"
                      class="form-control"
                      placeholder="ស្វែងរកតាមប្រធានបទ អ្នកដឹកនាំ ឬអ្នកស្នើសុំ..."
                      v-model="manageSearch"
                      @keyup.enter="fetchManageBookings"
                    />
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" @click="fetchManageBookings">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                  <select
                    class="custom-select custom-select-sm"
                    v-model="manageStatusFilter"
                    @change="fetchManageBookings"
                  >
                    <option value="">គ្រប់ស្ថានភាព</option>
                    <option value="PENDING">⚠️ រង់ចាំពិនិត្យ & ចាត់ចែង (Pending)</option>
                    <option value="APPROVED">✅ បានអនុម័ត (Approved)</option>
                    <option value="REJECTED">❌ បានបដិសេធ (Rejected)</option>
                    <option value="CANCELLED">⚪ បានបោះបង់ (Cancelled)</option>
                  </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                  <select
                    class="custom-select custom-select-sm"
                    v-model="manageRoomFilter"
                    @change="fetchManageBookings"
                  >
                    <option value="">🏢 គ្រប់បន្ទប់</option>
                    <option v-for="r in rooms" :key="r.id" :value="r.id">
                      {{ r.name }}
                    </option>
                  </select>
                </div>

                <div class="col-md-2 text-md-right">
                  <button class="btn btn-sm btn-outline-secondary" @click="fetchManageBookings" title="ផ្ទុកឡើងវិញ">
                    <i class="fas fa-sync-alt mr-1"></i> ផ្ទុកឡើងវិញ
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Pending Alert Banner -->
          <div v-if="pendingApprovalsCount > 0 && manageStatusFilter !== 'APPROVED'" class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-3">
            <i class="fas fa-exclamation-triangle fa-2x mr-3 text-warning"></i>
            <div>
              <div class="font-weight-bold">មានការស្នើសុំកក់បន្ទប់ចំនួន {{ toKhmerNum(pendingApprovalsCount) }} កំពុងរង់ចាំការពិនិត្យ!</div>
              <small class="text-dark">សូមពិនិត្យមើលភាពទំនេរនៃបន្ទប់ប្រជុំ និងកំណត់បន្ទប់ជូនអ្នកស្នើសុំ (Approve & Assign Room) ឬបដិសេធ។</small>
            </div>
          </div>

          <!-- Manage Bookings Table -->
          <div class="card shadow-sm border-0 rounded-lg overflow-hidden">
            <div class="card-body p-0 table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary">
                  <tr>
                    <th style="width: 150px;">អ្នកស្នើសុំ</th>
                    <th style="width: 140px;">កាលបរិច្ឆេទ</th>
                    <th style="width: 130px;">ម៉ោង</th>
                    <th>ប្រធានបទ & អ្នកដឹកនាំ</th>
                    <th style="width: 170px;">បន្ទប់ / បន្ទប់ចង់បាន</th>
                    <th style="width: 100px;">អ្នកចូលរួម</th>
                    <th style="width: 130px;">ស្ថានភាព</th>
                    <th style="width: 200px;" class="text-center">សកម្មភាពចាត់ចែង</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="loadingManageBookings">
                    <td colspan="8" class="text-center py-4 text-muted">
                      <div class="spinner-border spinner-border-sm text-primary mr-1"></div> កំពុងផ្ទុកទិន្នន័យ...
                    </td>
                  </tr>
                  <tr v-else-if="manageBookings.length === 0">
                    <td colspan="8" class="text-center py-5 text-muted">
                      <i class="fas fa-clipboard-check fa-2x mb-2 d-block text-secondary"></i>
                      មិនមានទិន្នន័យការស្នើសុំកក់បន្ទប់ឡើយ
                    </td>
                  </tr>
                  <tr v-for="b in manageBookings" :key="b.id">
                    <td>
                      <div class="font-weight-bold text-dark">{{ b.user?.name_kh || b.user?.name }}</div>
                      <div class="small text-muted">{{ b.user?.email }}</div>
                    </td>
                    <td class="font-weight-bold text-dark">
                      {{ formatKhmerDate(b.start_datetime) }}
                    </td>
                    <td>
                      <span class="badge badge-light border">
                        {{ formatTimeOnly(b.start_datetime) }} - {{ formatTimeOnly(b.end_datetime) }}
                      </span>
                    </td>
                    <td>
                      <div class="font-weight-bold text-dark">{{ b.subject }}</div>
                      <div class="small text-muted" v-if="b.leader">
                        <i class="fas fa-user-tie mr-1 text-secondary"></i> {{ b.leader }}
                      </div>
                      <div class="small text-muted" v-if="b.required_equipment && b.required_equipment.length > 0">
                        <i class="fas fa-tools mr-1 text-warning"></i> {{ b.required_equipment.join(', ') }}
                      </div>
                    </td>
                    <td>
                      <div v-if="b.room">
                        <span
                          class="badge px-2 py-1 text-white"
                          :style="{ backgroundColor: b.room.color || '#3b82f6' }"
                        >
                          <i class="fas fa-door-open mr-1"></i> {{ b.room.name }}
                        </span>
                        <div class="small text-muted mt-1">{{ b.room.location }}</div>
                      </div>
                      <div v-else>
                        <span class="badge badge-warning text-dark">រង់ចាំកំណត់បន្ទប់</span>
                        <div v-if="b.preferred_room" class="small text-muted mt-1">
                          <i class="fas fa-heart text-danger mr-1"></i> ចង់បាន៖ {{ b.preferred_room.name }}
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge badge-light border">
                        <i class="fas fa-users mr-1"></i> {{ toKhmerNum(b.participants_count || 0) }} នាក់
                      </span>
                    </td>
                    <td>
                      <span :class="getStatusBadgeClass(b.status)">
                        {{ b.status_khmer || b.status }}
                      </span>
                    </td>
                    <td class="text-center">
                      <!-- Actions when PENDING -->
                      <div v-if="b.status === 'PENDING'" class="btn-group btn-group-sm">
                        <button
                          class="btn btn-success font-weight-bold"
                          @click="openApprovalModal(b)"
                          title="ពិនិត្យភាពទំនេរ និងចាត់ចែងបន្ទប់"
                        >
                          <i class="fas fa-check-circle mr-1"></i> អនុម័ត & កំណត់បន្ទប់
                        </button>
                        <button
                          class="btn btn-danger font-weight-bold"
                          @click="openRejectModal(b)"
                          title="បដិសេធ"
                        >
                          <i class="fas fa-times-circle"></i>
                        </button>
                      </div>

                      <!-- Actions when APPROVED or other -->
                      <div v-else class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" @click="openBookingDetailModal(b)" title="មើលព័ត៌មានលម្អិត">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button
                          v-if="b.status === 'APPROVED'"
                          class="btn btn-outline-warning text-dark"
                          @click="openApprovalModal(b)"
                          title="ប្តូរបន្ទប់ប្រជុំ"
                        >
                          <i class="fas fa-exchange-alt mr-1"></i> ប្តូរបន្ទប់
                        </button>
                        <button
                          class="btn btn-outline-danger"
                          @click="confirmDeleteBooking(b)"
                          title="លុបកំណត់ត្រា"
                        >
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
    <!-- MODAL 1: CREATE / EDIT BOOKING REQUEST -->
    <!-- ============================================================= -->
    <div v-if="showBookingModal" class="custom-modal-backdrop" @click.self="closeBookingModal">
      <div class="modal-dialog modal-lg modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 800px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow-lg">
          <div class="modal-header bg-primary text-white py-3">
            <h5 class="modal-title font-weight-bold">
              <i class="fas" :class="isEditingBooking ? 'fa-edit' : 'fa-calendar-plus'"></i>
              {{ isEditingBooking ? 'កែសម្រួលការស្នើសុំកក់បន្ទប់' : 'ទម្រង់ស្នើសុំកក់បន្ទប់ប្រជុំ' }}
            </h5>
            <button type="button" class="close text-white" @click="closeBookingModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
            <!-- Help banner -->
            <div class="alert alert-light border shadow-sm small text-muted mb-3">
              <i class="fas fa-info-circle text-primary mr-1"></i>
              បន្ទាប់ពីដាក់ពាក្យស្នើសុំ អ្នកគ្រប់គ្រងបន្ទប់នឹងពិនិត្យមើលភាពទំនេរ និងចាត់ចែងបន្ទប់ប្រជុំដែលសមស្របជូនលោកអ្នក។
            </div>

            <form @submit.prevent="submitBookingForm">
              <!-- Meeting Subject -->
              <div class="form-group">
                <label class="font-weight-bold">
                  ប្រធានបទកិច្ចប្រជុំ <span class="text-danger">*</span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  v-model="bookingForm.subject"
                  required
                  placeholder="ឧទាហរណ៍៖ កិច្ចប្រជុំបូកសរុបការងារប្រចាំខែ..."
                />
              </div>

              <!-- Meeting Leader -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-user-tie text-secondary mr-1"></i> អ្នកដឹកនាំកិច្ចប្រជុំ / ប្រធានអង្គប្រជុំ <span class="text-danger">*</span>
                </label>
                <input
                  type="text"
                  class="form-control"
                  v-model="bookingForm.leader"
                  required
                  placeholder="ឧទាហរណ៍៖ ឯកឧត្តមប្រតិភូរាជរដ្ឋាភិបាល..."
                />
              </div>

              <!-- Date & Times -->
              <div class="row">
                <div class="col-md-4 form-group">
                  <label class="font-weight-bold">
                    <i class="far fa-calendar-alt text-primary mr-1"></i> កាលបរិច្ឆេទប្រជុំ <span class="text-danger">*</span>
                  </label>
                  <input
                    type="date"
                    class="form-control"
                    v-model="bookingForm.meeting_date"
                    required
                  />
                </div>

                <div class="col-md-4 form-group">
                  <label class="font-weight-bold">
                    <i class="far fa-clock text-success mr-1"></i> ម៉ោងចាប់ផ្តើម <span class="text-danger">*</span>
                  </label>
                  <input
                    type="time"
                    class="form-control"
                    v-model="bookingForm.start_time"
                    required
                  />
                </div>

                <div class="col-md-4 form-group">
                  <label class="font-weight-bold">
                    <i class="far fa-clock text-danger mr-1"></i> ម៉ោងបញ្ចប់ <span class="text-danger">*</span>
                  </label>
                  <input
                    type="time"
                    class="form-control"
                    v-model="bookingForm.end_time"
                    required
                  />
                </div>
              </div>

              <!-- Participants & Preferred Room -->
              <div class="row">
                <div class="col-md-6 form-group">
                  <label class="font-weight-bold">
                    <i class="fas fa-users text-info mr-1"></i> ចំនួនអ្នកចូលរួមប្រហាក់ប្រហែល
                  </label>
                  <input
                    type="number"
                    min="1"
                    class="form-control"
                    v-model="bookingForm.participants_count"
                    placeholder="ឧ. 15 នាក់"
                  />
                </div>

                <div class="col-md-6 form-group">
                  <label class="font-weight-bold">
                    <i class="fas fa-heart text-danger mr-1"></i> បន្ទប់ដែលចង់បាន (បើមានចំណូលចិត្ត)
                  </label>
                  <select class="custom-select" v-model="bookingForm.preferred_room_id">
                    <option :value="null">-- ទុកឱ្យអ្នកគ្រប់គ្រងចាត់ចែងបន្ទប់សមស្រប --</option>
                    <option v-for="r in rooms" :key="r.id" :value="r.id">
                      {{ r.name }} (ចំណុះ {{ toKhmerNum(r.capacity) }} នាក់) - {{ r.location }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Required Equipment (Checkboxes) -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-tools text-warning mr-1"></i> សម្ភារបរិក្ខារត្រូវការសម្រាប់កិច្ចប្រជុំ
                </label>
                <div class="row">
                  <div class="col-md-4 col-6 mb-2" v-for="eq in standardEquipmentList" :key="eq">
                    <div class="custom-control custom-checkbox">
                      <input
                        type="checkbox"
                        class="custom-control-input"
                        :id="'eq_' + eq"
                        :value="eq"
                        v-model="bookingForm.required_equipment"
                      />
                      <label class="custom-control-label small font-weight-bold" :for="'eq_' + eq">
                        {{ eq }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Meeting Link (Online option) -->
              <div class="form-group">
                <label class="font-weight-bold">
                  <i class="fas fa-video text-primary mr-1"></i> តំណភ្ជាប់ប្រជុំ Online (Link Zoom / Google Meet - បើមាន)
                </label>
                <input
                  type="url"
                  class="form-control"
                  v-model="bookingForm.meeting_link"
                  placeholder="https://meet.google.com/... ឬ Zoom link"
                />
              </div>

              <!-- Notes / Agenda -->
              <div class="form-group mb-0">
                <label class="font-weight-bold">
                  <i class="fas fa-comment-alt text-secondary mr-1"></i> កំណត់សម្គាល់បន្ថែម ឬរបៀបវារៈ
                </label>
                <textarea
                  class="form-control"
                  rows="3"
                  v-model="bookingForm.notes"
                  placeholder="ព័ត៌មានលម្អិតបន្ថែម ឬការរៀបចំពិសេស..."
                ></textarea>
              </div>
            </form>
          </div>

          <!-- Sticky Footer -->
          <div class="modal-footer sticky-modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-secondary px-3" @click="closeBookingModal">
              <i class="fas fa-times mr-1"></i> បោះបង់
            </button>
            <button
              type="button"
              class="btn btn-primary px-4"
              :disabled="savingBooking"
              @click="submitBookingForm"
            >
              <span v-if="savingBooking" class="spinner-border spinner-border-sm mr-1" role="status"></span>
              <i v-else class="fas fa-paper-plane mr-1"></i>
              {{ isEditingBooking ? 'រក្សាទុកការកែប្រែ' : 'ដាក់ពាក្យស្នើសុំកក់' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL 2: 🛡️ APPROVE & ASSIGN ROOM MODAL (For Managers/Admin) -->
    <!-- ============================================================= -->
    <div v-if="showApprovalModal" class="custom-modal-backdrop" @click.self="closeApprovalModal">
      <div class="modal-dialog modal-lg modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 820px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow-lg" v-if="approvalTargetBooking">
          <div class="modal-header bg-success text-white py-3">
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-check-double mr-2"></i> ពិនិត្យភាពទំនេរ & កំណត់បន្ទប់ប្រជុំ (Approve & Assign Room)
            </h5>
            <button type="button" class="close text-white" @click="closeApprovalModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
            <!-- Meeting Summary Card -->
            <div class="card bg-light border-0 shadow-sm mb-3">
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-md-8">
                    <h5 class="font-weight-bold text-dark mb-1">
                      {{ approvalTargetBooking.subject }}
                    </h5>
                    <div class="text-secondary small mb-2">
                      <i class="fas fa-user-tie mr-1"></i> អ្នកដឹកនាំ៖ <strong>{{ approvalTargetBooking.leader }}</strong>
                    </div>
                    <div class="small text-muted">
                      <i class="fas fa-user mr-1"></i> ស្នើសុំដោយ៖ <strong>{{ approvalTargetBooking.user?.name_kh || approvalTargetBooking.user?.name }}</strong> ({{ approvalTargetBooking.user?.email }})
                    </div>
                  </div>
                  <div class="col-md-4 text-md-right mt-2 mt-md-0">
                    <div class="badge badge-primary px-2 py-1 mb-1">
                      <i class="far fa-calendar-alt mr-1"></i> {{ formatKhmerDate(approvalTargetBooking.start_datetime) }}
                    </div>
                    <div class="badge badge-light border text-dark d-block">
                      <i class="far fa-clock mr-1"></i> {{ formatTimeOnly(approvalTargetBooking.start_datetime) }} - {{ formatTimeOnly(approvalTargetBooking.end_datetime) }}
                    </div>
                    <div class="small text-muted mt-1">
                      <i class="fas fa-users mr-1"></i> អ្នកចូលរួម៖ {{ toKhmerNum(approvalTargetBooking.participants_count || 0) }} នាក់
                    </div>
                  </div>
                </div>

                <div v-if="approvalTargetBooking.required_equipment && approvalTargetBooking.required_equipment.length > 0" class="mt-2 pt-2 border-top small">
                  <span class="font-weight-bold text-dark mr-1">ឧបករណ៍ត្រូវការ៖</span>
                  <span
                    v-for="(eq, eqIdx) in approvalTargetBooking.required_equipment"
                    :key="eqIdx"
                    class="badge badge-light border mr-1 text-secondary"
                  >
                    {{ eq }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Real-time Room Availability Check -->
            <div class="mb-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="font-weight-bold text-dark mb-0">
                  <i class="fas fa-door-closed text-primary mr-1"></i> ជ្រើសរើសបន្ទប់ប្រជុំសម្រាប់កិច្ចប្រជុំនេះ <span class="text-danger">*</span>
                </h6>
                <span v-if="loadingAvailability" class="text-muted small">
                  <span class="spinner-border spinner-border-sm text-primary mr-1"></span> កំពុងពិនិត្យភាពទំនេរ...
                </span>
              </div>

              <!-- Rooms selection list with conflict status -->
              <div v-if="loadingAvailability" class="text-center py-4 bg-light rounded border mb-3">
                <span class="spinner-border spinner-border-sm text-primary mr-2"></span>
                <span class="text-muted small font-khmer">កំពុងទាញយក និងពិនិត្យមើលភាពទំនេរនៃបន្ទប់ប្រជុំ...</span>
              </div>
              <div v-else-if="roomAvailabilityList.length === 0" class="alert alert-warning py-3 text-center mb-3 font-khmer">
                <i class="fas fa-exclamation-circle mr-1"></i> មិនទាន់មានបន្ទប់ប្រជុំសកម្មក្នុងប្រព័ន្ធនៅឡើយទេ។
              </div>
              <div v-else class="row">
                <div v-if="roomAvailabilityList.every(r => !r.is_available)" class="col-12 mb-3">
                  <div class="alert alert-danger py-2 px-3 small mb-0 font-khmer">
                    <i class="fas fa-exclamation-triangle mr-1"></i> <strong>សូមជ្រាប៖</strong> បន្ទប់ប្រជុំទាំងអស់កំពុងជាប់កិច្ចប្រជុំនៅចន្លោះម៉ោងនេះ! លោកអ្នកអាចបដិសេធសំណើ ឬទាក់ទងអ្នកស្នើសុំដើម្បីប្តូរម៉ោង។
                  </div>
                </div>
                <div v-for="room in roomAvailabilityList" :key="room.id" class="col-md-6 mb-2">
                  <div
                    class="card border p-3 room-select-card h-100 cursor-pointer"
                    :class="{
                      'border-primary bg-primary-soft shadow-sm selected': selectedApprovalRoomId === room.id,
                      'border-danger bg-danger-soft opacity-75': !room.is_available,
                      'border-secondary': room.is_available && selectedApprovalRoomId !== room.id
                    }"
                    @click="room.is_available && (selectedApprovalRoomId = room.id)"
                  >
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <div class="d-flex align-items-center">
                        <input
                          type="radio"
                          name="approvalRoom"
                          :id="'appRoom_' + room.id"
                          :value="room.id"
                          v-model="selectedApprovalRoomId"
                          :disabled="!room.is_available"
                          class="mr-2"
                        />
                        <label :for="'appRoom_' + room.id" class="font-weight-bold text-dark mb-0 cursor-pointer">
                          {{ room.name }}
                        </label>
                      </div>

                      <span v-if="room.is_available" class="badge badge-success px-2 py-1">
                        <i class="fas fa-check-circle mr-1"></i> ទំនេរ
                      </span>
                      <span v-else class="badge badge-danger px-2 py-1">
                        <i class="fas fa-times-circle mr-1"></i> ជាន់ម៉ោង
                      </span>
                    </div>

                    <div class="small text-muted mb-1">
                      <i class="fas fa-map-marker-alt text-danger mr-1"></i> {{ room.location }}
                    </div>

                    <div class="small mb-1">
                      <span class="badge badge-light border text-dark font-weight-bold">
                        <i class="fas fa-users text-primary mr-1"></i> ចំណុះ {{ toKhmerNum(room.capacity) }} នាក់
                      </span>
                      <span
                        v-if="approvalTargetBooking.participants_count > room.capacity"
                        class="badge badge-warning ml-1 text-dark"
                        title="ចំនួនអ្នកចូលរួមលើសពីចំណុះបន្ទប់"
                      >
                        ⚠️ លើស {{ toKhmerNum(approvalTargetBooking.participants_count - room.capacity) }} នាក់
                      </span>
                    </div>

                    <!-- Conflict details if any -->
                    <div v-if="!room.is_available && ((room.conflicts && room.conflicts.length > 0) || room.conflict)" class="mt-2 p-2 bg-white rounded border border-danger text-danger small">
                      <div class="font-weight-bold">
                        <i class="fas fa-exclamation-triangle mr-1"></i> កំពុងជាប់កិច្ចប្រជុំ៖
                      </div>
                      <div v-for="c in (room.conflicts && room.conflicts.length > 0 ? room.conflicts : [room.conflict])" :key="c.id" class="mt-1 text-truncate">
                        • <strong>{{ c.subject || c.title }}</strong> ({{ c.start_time || formatTimeOnly(c.start_datetime) }} - {{ c.end_time || formatTimeOnly(c.end_datetime) }})
                        <span v-if="c.leader || c.leader_name" class="text-muted ml-1">[{{ c.leader || c.leader_name }}]</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Admin Note / Message to Requester -->
            <div class="form-group mb-0">
              <label class="font-weight-bold">
                <i class="fas fa-sticky-note text-secondary mr-1"></i> កំណត់សម្គាល់ពីអ្នកគ្រប់គ្រង (បញ្ជូនទៅកាន់អ្នកស្នើសុំ)
              </label>
              <textarea
                class="form-control"
                rows="2"
                v-model="approvalAdminNote"
                placeholder="ឧទាហរណ៍៖ បានរៀបចំបន្ទប់ និងឧបករណ៍ Projector រួចរាល់..."
              ></textarea>
            </div>
          </div>

          <!-- Sticky Modal Footer -->
          <div class="modal-footer sticky-modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-secondary px-3" @click="closeApprovalModal">
              <i class="fas fa-times mr-1"></i> បោះបង់
            </button>
            <button
              type="button"
              class="btn btn-success px-4 font-weight-bold"
              :disabled="savingApproval || !selectedApprovalRoomId"
              @click="submitApproval"
            >
              <span v-if="savingApproval" class="spinner-border spinner-border-sm mr-1" role="status"></span>
              <i v-else class="fas fa-check-circle mr-1"></i>
              បញ្ជាក់ការអនុម័ត និងកំណត់បន្ទប់
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL 3: ❌ REJECT BOOKING REQUEST MODAL -->
    <!-- ============================================================= -->
    <div v-if="showRejectModal" class="custom-modal-backdrop" @click.self="closeRejectModal">
      <div class="modal-dialog modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 550px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow-lg" v-if="rejectTargetBooking">
          <div class="modal-header bg-danger text-white py-3">
            <h5 class="modal-title font-weight-bold">
              <i class="fas fa-times-circle mr-2"></i> បដិសេធការស្នើសុំកក់បន្ទប់
            </h5>
            <button type="button" class="close text-white" @click="closeRejectModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
            <p class="text-dark mb-3">
              តើលោកអ្នកពិតជាចង់បដិសេធការស្នើសុំកិច្ចប្រជុំ <strong class="text-danger">"{{ rejectTargetBooking.subject }}"</strong> របស់ <strong>{{ rejectTargetBooking.user?.name_kh || rejectTargetBooking.user?.name }}</strong> មែនទេ?
            </p>

            <!-- Quick reason presets -->
            <div class="mb-3">
              <label class="small font-weight-bold text-muted mb-1">មូលហេតុរហ័ស៖</label>
              <div class="d-flex flex-wrap gap-1">
                <button
                  type="button"
                  class="btn btn-xs btn-outline-secondary mr-1 mb-1"
                  v-for="rPreset in rejectPresets"
                  :key="rPreset"
                  @click="rejectionReason = rPreset"
                >
                  {{ rPreset }}
                </button>
              </div>
            </div>

            <div class="form-group mb-0">
              <label class="font-weight-bold">
                មូលហេតុនៃការបដិសេធ <span class="text-danger">*</span>
              </label>
              <textarea
                class="form-control"
                rows="3"
                v-model="rejectionReason"
                required
                placeholder="បញ្ជាក់មូលហេតុដែលមិនអាចអនុម័តបន្ទប់បាន..."
              ></textarea>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer sticky-modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-secondary px-3" @click="closeRejectModal">
              បោះបង់
            </button>
            <button
              type="button"
              class="btn btn-danger px-4 font-weight-bold"
              :disabled="savingReject || !rejectionReason.trim()"
              @click="submitReject"
            >
              <span v-if="savingReject" class="spinner-border spinner-border-sm mr-1" role="status"></span>
              <i v-else class="fas fa-ban mr-1"></i> បញ្ជាក់ការបដិសេធ
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL 4: 👁️ BOOKING DETAIL MODAL -->
    <!-- ============================================================= -->
    <div v-if="showDetailModal" class="custom-modal-backdrop" @click.self="closeDetailModal">
      <div class="modal-dialog modal-lg modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 700px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow-lg" v-if="selectedBooking">
          <div class="modal-header bg-light py-3 border-bottom">
            <h5 class="modal-title font-weight-bold text-dark">
              <i class="fas fa-info-circle text-primary mr-2"></i> ព័ត៌មានលម្អិតនៃការកក់បន្ទប់
            </h5>
            <button type="button" class="close text-dark" @click="closeDetailModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
            <!-- Status & Title -->
            <div class="d-flex justify-content-between align-items-start mb-3">
              <div>
                <span :class="getStatusBadgeClass(selectedBooking.status)" class="mb-2 d-inline-block">
                  {{ selectedBooking.status_khmer || selectedBooking.status }}
                </span>
                <h4 class="font-weight-bold text-dark mb-0">
                  {{ selectedBooking.subject }}
                </h4>
              </div>
            </div>

            <!-- Detail Grid -->
            <div class="row mb-3 bg-light p-3 rounded">
              <div class="col-md-6 mb-2">
                <small class="text-muted d-block">កាលបរិច្ឆេទ & ពេលវេលា៖</small>
                <div class="font-weight-bold text-primary">
                  <i class="far fa-calendar-alt mr-1"></i> {{ formatKhmerDate(selectedBooking.start_datetime) }}
                </div>
                <div class="small font-weight-bold text-dark">
                  <i class="far fa-clock mr-1"></i> {{ formatTimeOnly(selectedBooking.start_datetime) }} - {{ formatTimeOnly(selectedBooking.end_datetime) }}
                </div>
              </div>

              <div class="col-md-6 mb-2">
                <small class="text-muted d-block">បន្ទប់ប្រជុំដែលបានកំណត់៖</small>
                <div v-if="selectedBooking.room" class="font-weight-bold">
                  <span class="badge px-2 py-1 text-white" :style="{ backgroundColor: selectedBooking.room.color || '#3b82f6' }">
                    <i class="fas fa-door-open mr-1"></i> {{ selectedBooking.room.name }}
                  </span>
                  <div class="small text-muted mt-1">{{ selectedBooking.room.location }}</div>
                </div>
                <div v-else class="text-warning small font-weight-bold">
                  <i class="fas fa-hourglass-half mr-1"></i> រង់ចាំការកំណត់បន្ទប់ពីអ្នកគ្រប់គ្រង
                </div>
              </div>

              <div class="col-md-6 mb-2">
                <small class="text-muted d-block">អ្នកដឹកនាំអង្គប្រជុំ៖</small>
                <div class="font-weight-bold text-dark">
                  <i class="fas fa-user-tie text-secondary mr-1"></i> {{ selectedBooking.leader || '-' }}
                </div>
              </div>

              <div class="col-md-6 mb-2">
                <small class="text-muted d-block">ចំនួនអ្នកចូលរួម៖</small>
                <div class="font-weight-bold text-dark">
                  <i class="fas fa-users text-info mr-1"></i> {{ toKhmerNum(selectedBooking.participants_count || 0) }} នាក់
                </div>
              </div>

              <div class="col-md-6 mb-2">
                <small class="text-muted d-block">អ្នកស្នើសុំកក់៖</small>
                <div class="font-weight-bold text-dark">
                  <i class="fas fa-user text-secondary mr-1"></i> {{ selectedBooking.user?.name_kh || selectedBooking.user?.name }}
                </div>
                <div class="small text-muted">{{ selectedBooking.user?.email }}</div>
              </div>

              <div class="col-md-6 mb-2" v-if="selectedBooking.approver">
                <small class="text-muted d-block">អ្នកពិនិត្យ / អនុម័ត៖</small>
                <div class="font-weight-bold text-dark">
                  <i class="fas fa-user-shield text-success mr-1"></i> {{ selectedBooking.approver.name_kh || selectedBooking.approver.name }}
                </div>
                <div class="small text-muted" v-if="selectedBooking.approved_at">
                  {{ formatKhmerDate(selectedBooking.approved_at) }}
                </div>
              </div>
            </div>

            <!-- Required Equipment -->
            <div v-if="selectedBooking.required_equipment && selectedBooking.required_equipment.length > 0" class="mb-3">
              <h6 class="font-weight-bold text-dark mb-2">
                <i class="fas fa-tools text-warning mr-1"></i> សម្ភារបរិក្ខារត្រូវការ៖
              </h6>
              <div class="d-flex flex-wrap gap-1">
                <span
                  v-for="(eq, eqIdx) in selectedBooking.required_equipment"
                  :key="eqIdx"
                  class="badge badge-light border text-secondary mr-1 mb-1"
                >
                  {{ eq }}
                </span>
              </div>
            </div>

            <!-- Meeting Link -->
            <div v-if="selectedBooking.meeting_link" class="mb-3">
              <h6 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-video text-primary mr-1"></i> តំណភ្ជាប់ប្រជុំ Online៖
              </h6>
              <a :href="selectedBooking.meeting_link" target="_blank" class="text-primary text-break">
                {{ selectedBooking.meeting_link }}
              </a>
            </div>

            <!-- Admin Note -->
            <div v-if="selectedBooking.admin_note" class="alert alert-info border-0 shadow-sm mb-3">
              <div class="font-weight-bold text-info">
                <i class="fas fa-comment-dots mr-1"></i> កំណត់សម្គាល់ពីអ្នកគ្រប់គ្រង៖
              </div>
              <div class="small mt-1 text-dark">{{ selectedBooking.admin_note }}</div>
            </div>

            <!-- Rejection Reason -->
            <div v-if="selectedBooking.status === 'REJECTED' && selectedBooking.rejection_reason" class="alert alert-danger border-0 shadow-sm mb-3">
              <div class="font-weight-bold text-danger">
                <i class="fas fa-exclamation-circle mr-1"></i> មូលហេតុនៃការបដិសេធ៖
              </div>
              <div class="small mt-1 text-dark">{{ selectedBooking.rejection_reason }}</div>
            </div>

            <!-- Notes -->
            <div v-if="selectedBooking.notes" class="mb-0">
              <h6 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-align-left text-muted mr-1"></i> កំណត់សម្គាល់ / របៀបវារៈ៖
              </h6>
              <div class="p-2 bg-light rounded text-secondary small text-pre-wrap">
                {{ selectedBooking.notes }}
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="modal-footer sticky-modal-footer d-flex justify-content-end">
            <button type="button" class="btn btn-secondary px-3" @click="closeDetailModal">
              បិទ
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL 5: 🏢 CREATE / EDIT MEETING ROOM (Manager / Admin) -->
    <!-- ============================================================= -->
    <div v-if="showRoomModal" class="custom-modal-backdrop" @click.self="closeRoomModal">
      <div class="modal-dialog modal-lg modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 750px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow-lg">
          <div class="modal-header bg-dark text-white py-3">
            <h5 class="modal-title font-weight-bold">
              <i class="fas" :class="isEditingRoom ? 'fa-edit' : 'fa-plus-circle'"></i>
              {{ isEditingRoom ? 'កែសម្រួលព័ត៌មានបន្ទប់ប្រជុំ' : 'បង្កើតបន្ទប់ប្រជុំថ្មី' }}
            </h5>
            <button type="button" class="close text-white" @click="closeRoomModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
            <form @submit.prevent="submitRoomForm">
              <!-- Name & Location -->
              <div class="row">
                <div class="col-md-7 form-group">
                  <label class="font-weight-bold">
                    ឈ្មោះបន្ទប់ប្រជុំ <span class="text-danger">*</span>
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="roomForm.name"
                    required
                    placeholder="ឧទាហរណ៍៖ បន្ទប់ប្រជុំធំ A"
                  />
                </div>

                <div class="col-md-5 form-group">
                  <label class="font-weight-bold">
                    ទីតាំង / អគារ <span class="text-danger">*</span>
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    v-model="roomForm.location"
                    required
                    placeholder="ឧ. អគារ A ជាន់ទី២"
                  />
                </div>
              </div>

              <!-- Capacity, Color & Status -->
              <div class="row">
                <div class="col-md-4 form-group">
                  <label class="font-weight-bold">
                    ចំណុះ (ចំនួនមនុស្ស) <span class="text-danger">*</span>
                  </label>
                  <input
                    type="number"
                    min="1"
                    class="form-control"
                    v-model="roomForm.capacity"
                    required
                  />
                </div>

                <div class="col-md-4 form-group">
                  <label class="font-weight-bold">ស្ថានភាព</label>
                  <select class="custom-select" v-model="roomForm.status">
                    <option value="ACTIVE">សកម្ម (Active)</option>
                    <option value="MAINTENANCE">ជួសជុល / ផ្អាក (Maintenance)</option>
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
                      :class="{ 'active': roomForm.color === color }"
                      @click="roomForm.color = color"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- Facilities checklist -->
              <div class="form-group">
                <label class="font-weight-bold">
                  សម្ភារបរិក្ខារបំពាក់ក្នុងបន្ទប់
                </label>
                <div class="row">
                  <div class="col-md-4 col-6 mb-2" v-for="fac in standardFacilitiesList" :key="fac">
                    <div class="custom-control custom-checkbox">
                      <input
                        type="checkbox"
                        class="custom-control-input"
                        :id="'rf_' + fac"
                        :value="fac"
                        v-model="roomForm.facilities"
                      />
                      <label class="custom-control-label small font-weight-bold" :for="'rf_' + fac">
                        {{ fac }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Room Description -->
              <div class="form-group mb-0">
                <label class="font-weight-bold">ការពិពណ៌នាពីបន្ទប់</label>
                <textarea
                  class="form-control"
                  rows="3"
                  v-model="roomForm.description"
                  placeholder="ព័ត៌មានលម្អិតពីបន្ទប់ បរិយាកាស ឬលក្ខខណ្ឌប្រើប្រាស់..."
                ></textarea>
              </div>
            </form>
          </div>

          <!-- Sticky Footer -->
          <div class="modal-footer sticky-modal-footer d-flex justify-content-between">
            <button type="button" class="btn btn-secondary px-3" @click="closeRoomModal">
              <i class="fas fa-times mr-1"></i> បោះបង់
            </button>
            <button
              type="button"
              class="btn btn-primary px-4"
              :disabled="savingRoom"
              @click="submitRoomForm"
            >
              <span v-if="savingRoom" class="spinner-border spinner-border-sm mr-1" role="status"></span>
              <i v-else class="fas fa-save mr-1"></i>
              {{ isEditingRoom ? 'រក្សាទុកការកែប្រែ' : 'បង្កើតបន្ទប់' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ============================================================= -->
    <!-- MODAL 6: DAY EVENTS MODAL (Calendar view day click) -->
    <!-- ============================================================= -->
    <div v-if="showDayEventsModal" class="custom-modal-backdrop" @click.self="closeDayEventsModal">
      <div class="modal-dialog modal-dialog-centered font-khmer my-auto" role="document" style="width: 100%; max-width: 600px;">
        <div class="modal-content custom-modal-content d-flex flex-column h-100 overflow-hidden font-khmer shadow-lg" v-if="selectedDay">
          <div class="modal-header bg-light py-3 border-bottom">
            <h5 class="modal-title font-weight-bold text-dark">
              <i class="fas fa-calendar-day text-primary mr-2"></i>
              កាលវិភាគប្រជុំថ្ងៃទី {{ toKhmerNum(selectedDay.dayNumber) }} {{ formatKhmerMonthYear(currentYear, currentMonth) }}
            </h5>
            <button type="button" class="close text-dark" @click="closeDayEventsModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto;">
            <div v-for="booking in selectedDay.bookings" :key="booking.id" class="card shadow-sm border mb-2 p-2">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="font-weight-bold text-dark">{{ booking.subject }}</div>
                  <div class="small text-muted" v-if="booking.leader">
                    <i class="fas fa-user-tie mr-1"></i> {{ booking.leader }}
                  </div>
                  <div class="small mt-1">
                    <span class="badge px-2 py-1 text-white" :style="{ backgroundColor: booking.room?.color || '#3b82f6' }">
                      <i class="fas fa-door-open mr-1"></i> {{ booking.room?.name }}
                    </span>
                    <span class="badge badge-light border ml-1">
                      <i class="far fa-clock mr-1"></i> {{ formatTimeOnly(booking.start_datetime) }} - {{ formatTimeOnly(booking.end_datetime) }}
                    </span>
                  </div>
                </div>
                <button class="btn btn-sm btn-outline-info" @click="openBookingDetailModal(booking)">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="modal-footer sticky-modal-footer d-flex justify-content-between">
            <button class="btn btn-sm btn-success" @click="openCreateBookingModal(selectedDay.dateString)">
              <i class="fas fa-plus mr-1"></i> ស្នើសុំកក់លើថ្ងៃនេះ
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
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import Swal from 'sweetalert2';
import { useUserStore } from '@/stores/user';
import {
  apiGetMeetingRooms,
  apiCheckRoomAvailability,
  apiCreateMeetingRoom,
  apiUpdateMeetingRoom,
  apiDeleteMeetingRoom,
  apiGetRoomBookings,
  apiGetRoomTimetable,
  apiGetMyBookings,
  apiCreateRoomBooking,
  apiUpdateRoomBooking,
  apiCancelRoomBooking,
  apiDeleteRoomBooking,
  apiApproveRoomBooking,
  apiRejectRoomBooking
} from '@/functions/api/meetingRoom';

const route = useRoute();
const userStore = useUserStore();

// Check if current route is manage-meeting-rooms
const isManageRoute = computed(() => {
  return route.name === 'manage-meeting-rooms' || (route.path && route.path.includes('/manage/meeting-rooms')) || route.query?.tab === 'management';
});

// Check if user has management permissions for rooms
const canManage = computed(() => {
  if (isManageRoute.value) return true;
  if (userStore.isAdmin) return true;
  if (userStore.can('manage-meeting-rooms')) return true;
  // Check if current user is manager of any room
  return Array.isArray(rooms.value) && rooms.value.some(r => r.manager_id === userStore.id);
});

// Active Tab: 'timetable' | 'rooms' | 'my-bookings' | 'management'
const activeTab = ref(isManageRoute.value ? 'management' : 'timetable');

watch(
  () => [route.name, route.path, route.query],
  () => {
    if (isManageRoute.value) {
      activeTab.value = 'management';
    } else if (activeTab.value === 'management' && route.name === 'meeting-rooms' && !route.query?.tab) {
      activeTab.value = 'timetable';
    }
  }
);

// Watch activeTab to automatically refresh data when switching tabs
watch(activeTab, (newTab) => {
  if (newTab === 'timetable') {
    fetchTimetable();
  } else if (newTab === 'rooms') {
    fetchRooms();
  } else if (newTab === 'my-bookings') {
    fetchMyBookings();
  } else if (newTab === 'management') {
    fetchManageBookings();
  }
});

// View Mode for Timetable: 'calendar' | 'agenda'
const timetableViewMode = ref('calendar');

// Current Date for Calendar
const today = new Date();
const currentYear = ref(today.getFullYear());
const currentMonth = ref(today.getMonth() + 1); // 1 - 12

// Color swatches for rooms
const presetColors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#64748b'];

// Standard lists
const standardEquipmentList = [
  'Projector / អេក្រង់បញ្ចាំង',
  'មីក្រូហ្វូន & ប្រព័ន្ធសំឡេង',
  'Video Conference (Zoom/Meet)',
  'ក្តារខៀន (Whiteboard)',
  'High-speed WiFi',
  'កុំព្យូទ័រ Laptop',
  'ទឹកសុទ្ធ & កាហ្វេ'
];

const standardFacilitiesList = [
  'Projector / អេក្រង់បញ្ចាំង',
  'Smart TV 75"',
  'Sound System / មីក្រូហ្វូន',
  'Video Conference 4K',
  'Whiteboard',
  'Air Conditioner',
  'High-speed WiFi'
];

const rejectPresets = [
  'បន្ទប់ទាំងអស់មិនទំនេរនៅម៉ោងនេះទេ',
  'ជាន់កាលវិភាគកិច្ចប្រជុំបន្ទាន់របស់ថ្នាក់ដឹកនាំ',
  'ចំនួនអ្នកចូលរួមលើសពីចំណុះបន្ទប់ដែលមាន',
  'សូមមេត្តាជ្រើសរើសម៉ោង ឬកាលបរិច្ឆេទផ្សេង'
];

// Data States
const rooms = ref([]);
const loadingRooms = ref(false);

const timetableBookings = ref([]);
const loadingTimetable = ref(false);
const filterRoomId = ref('');

const myBookings = ref([]);
const loadingMyBookings = ref(false);
const myBookingsSearch = ref('');
const myBookingsStatusFilter = ref('');

const manageBookings = ref([]);
const loadingManageBookings = ref(false);
const manageSearch = ref('');
const manageStatusFilter = ref('');
const manageRoomFilter = ref('');

// Modals State
const showBookingModal = ref(false);
const isEditingBooking = ref(false);
const editingBookingId = ref(null);
const savingBooking = ref(false);
const bookingForm = ref({
  subject: '',
  leader: '',
  meeting_date: '',
  start_time: '08:30',
  end_time: '11:30',
  participants_count: null,
  preferred_room_id: null,
  required_equipment: [],
  meeting_link: '',
  notes: ''
});

// Approval Modal State
const showApprovalModal = ref(false);
const approvalTargetBooking = ref(null);
const roomAvailabilityList = ref([]);
const loadingAvailability = ref(false);
const selectedApprovalRoomId = ref(null);
const approvalAdminNote = ref('');
const savingApproval = ref(false);

// Reject Modal State
const showRejectModal = ref(false);
const rejectTargetBooking = ref(null);
const rejectionReason = ref('');
const savingReject = ref(false);

// Detail Modal State
const showDetailModal = ref(false);
const selectedBooking = ref(null);

// Room Form Modal State (Admin)
const showRoomModal = ref(false);
const isEditingRoom = ref(false);
const editingRoomId = ref(null);
const savingRoom = ref(false);
const roomForm = ref({
  name: '',
  location: '',
  capacity: 20,
  facilities: [],
  color: '#3b82f6',
  status: 'ACTIVE',
  manager_id: null,
  description: ''
});

// Day Events Modal State
const showDayEventsModal = ref(false);
const selectedDay = ref(null);

// Computed Statistics
const activeRoomsCount = computed(() => {
  if (!Array.isArray(rooms.value)) return 0;
  return rooms.value.filter(r => r.status === 'ACTIVE').length;
});

const approvedCountMonth = computed(() => {
  if (!Array.isArray(timetableBookings.value)) return 0;
  return timetableBookings.value.filter(b => b.status === 'APPROVED').length;
});

const pendingApprovalsCount = computed(() => {
  if (!Array.isArray(manageBookings.value)) return 0;
  return manageBookings.value.filter(b => b.status === 'PENDING').length;
});

const myBookingsCount = computed(() => {
  if (!Array.isArray(myBookings.value)) return 0;
  return myBookings.value.length;
});

// ==================== CALENDAR COMPUTATION ====================
const calendarDays = computed(() => {
  const year = currentYear.value;
  const month = currentMonth.value; // 1-12
  const firstDayOfMonth = new Date(year, month - 1, 1);
  const daysInMonth = new Date(year, month, 0).getDate();
  const startingDayOfWeek = firstDayOfMonth.getDay(); // 0 (Sun) to 6 (Sat)
  const prevMonthDays = new Date(year, month - 1, 0).getDate();

  const days = [];
  const bookingsList = Array.isArray(timetableBookings.value) ? timetableBookings.value : [];

  // Previous month trailing days
  for (let i = startingDayOfWeek - 1; i >= 0; i--) {
    const dayNum = prevMonthDays - i;
    const prevMonthVal = month === 1 ? 12 : month - 1;
    const prevYearVal = month === 1 ? year - 1 : year;
    const dateStr = `${prevYearVal}-${String(prevMonthVal).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
    const dateObj = new Date(prevYearVal, prevMonthVal - 1, dayNum);
    days.push({
      dateString: dateStr,
      dayNumber: dayNum,
      isCurrentMonth: false,
      isToday: isSameDay(dateObj, new Date()),
      isWeekend: dateObj.getDay() === 0 || dateObj.getDay() === 6,
      isSunday: dateObj.getDay() === 0,
      bookings: []
    });
  }

  // Current month days
  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
    const dateObj = new Date(year, month - 1, d);
    const dayBookings = bookingsList.filter(b => {
      if (b.status !== 'APPROVED') return false;
      const bDate = b.booking_date
        ? (typeof b.booking_date === 'string' ? b.booking_date.substring(0, 10) : '')
        : (b.start_datetime ? (typeof b.start_datetime === 'string' ? b.start_datetime.substring(0, 10) : '') : '');
      return bDate === dateStr;
    });

    days.push({
      dateString: dateStr,
      dayNumber: d,
      isCurrentMonth: true,
      isToday: isSameDay(dateObj, new Date()),
      isWeekend: dateObj.getDay() === 0 || dateObj.getDay() === 6,
      isSunday: dateObj.getDay() === 0,
      bookings: dayBookings
    });
  }

  // Next month leading days to complete grid (multiples of 7)
  const totalCells = Math.ceil(days.length / 7) * 7;
  const remaining = totalCells - days.length;
  for (let n = 1; n <= remaining; n++) {
    const nextMonthVal = month === 12 ? 1 : month + 1;
    const nextYearVal = month === 12 ? year + 1 : year;
    const dateStr = `${nextYearVal}-${String(nextMonthVal).padStart(2, '0')}-${String(n).padStart(2, '0')}`;
    const dateObj = new Date(nextYearVal, nextMonthVal - 1, n);
    days.push({
      dateString: dateStr,
      dayNumber: n,
      isCurrentMonth: false,
      isToday: isSameDay(dateObj, new Date()),
      isWeekend: dateObj.getDay() === 0 || dateObj.getDay() === 6,
      isSunday: dateObj.getDay() === 0,
      bookings: []
    });
  }

  return days;
});

function isSameDay(d1, d2) {
  return d1.getFullYear() === d2.getFullYear() &&
         d1.getMonth() === d2.getMonth() &&
         d1.getDate() === d2.getDate();
}

// ==================== API FETCHERS ====================

// Fetch meeting rooms
const fetchRooms = async () => {
  loadingRooms.value = true;
  try {
    const res = await apiGetMeetingRooms({ all: 1 });
    rooms.value = res.data?.data || res.data || [];
  } catch (error) {
    console.error('Failed to load meeting rooms:', error);
  } finally {
    loadingRooms.value = false;
  }
};

// Fetch timetable bookings for calendar
const fetchTimetable = async () => {
  loadingTimetable.value = true;
  try {
    const params = {
      year: currentYear.value,
      month: currentMonth.value,
      status: 'APPROVED',
    };
    if (filterRoomId.value) {
      params.room_id = filterRoomId.value;
    }
    const res = await apiGetRoomTimetable(params);
    const rawData = res.data?.data || res.data;
    let list = [];
    if (Array.isArray(rawData)) {
      list = rawData;
    } else if (rawData && Array.isArray(rawData.bookings)) {
      list = rawData.bookings;
    } else if (res.data && Array.isArray(res.data.bookings)) {
      list = res.data.bookings;
    }
    // Only display APPROVED bookings on the timetable
    timetableBookings.value = list.filter(b => b.status === 'APPROVED');
  } catch (error) {
    console.error('Failed to load timetable:', error);
    timetableBookings.value = [];
  } finally {
    loadingTimetable.value = false;
  }
};

// Fetch current user bookings
const fetchMyBookings = async () => {
  loadingMyBookings.value = true;
  try {
    const params = {};
    if (myBookingsSearch.value) params.search = myBookingsSearch.value;
    if (myBookingsStatusFilter.value) params.status = myBookingsStatusFilter.value;
    const res = await apiGetMyBookings(params);
    myBookings.value = res.data?.data || res.data || [];
  } catch (error) {
    console.error('Failed to load my bookings:', error);
  } finally {
    loadingMyBookings.value = false;
  }
};

// Fetch bookings for management tab
const fetchManageBookings = async () => {
  loadingManageBookings.value = true;
  try {
    const params = {};
    if (manageSearch.value) params.search = manageSearch.value;
    if (manageStatusFilter.value) params.status = manageStatusFilter.value;
    if (manageRoomFilter.value) params.room_id = manageRoomFilter.value;
    const res = await apiGetRoomBookings(params);
    manageBookings.value = res.data?.data || res.data || [];
  } catch (error) {
    console.error('Failed to load manage bookings:', error);
    manageBookings.value = [];
  } finally {
    loadingManageBookings.value = false;
  }
};

// ==================== CALENDAR NAVIGATION ====================
const prevMonth = () => {
  if (currentMonth.value === 1) {
    currentMonth.value = 12;
    currentYear.value -= 1;
  } else {
    currentMonth.value -= 1;
  }
  fetchTimetable();
};

const nextMonth = () => {
  if (currentMonth.value === 12) {
    currentMonth.value = 1;
    currentYear.value += 1;
  } else {
    currentMonth.value += 1;
  }
  fetchTimetable();
};

const goToToday = () => {
  const d = new Date();
  currentYear.value = d.getFullYear();
  currentMonth.value = d.getMonth() + 1;
  fetchTimetable();
};

// ==================== BOOKING MODAL ACTIONS ====================
const openCreateBookingModal = (dateStr = null, preferredRoomId = null) => {
  isEditingBooking.value = false;
  editingBookingId.value = null;

  const todayStr = new Date().toISOString().substring(0, 10);
  bookingForm.value = {
    subject: '',
    leader: '',
    meeting_date: dateStr || todayStr,
    start_time: '08:30',
    end_time: '11:30',
    participants_count: null,
    preferred_room_id: preferredRoomId || null,
    required_equipment: [],
    meeting_link: '',
    notes: ''
  };
  showBookingModal.value = true;
};

const openEditBookingModal = (booking) => {
  isEditingBooking.value = true;
  editingBookingId.value = booking.id;

  const dateStr = booking.start_datetime ? booking.start_datetime.substring(0, 10) : '';
  const startTime = booking.start_datetime ? booking.start_datetime.substring(11, 16) : '08:30';
  const endTime = booking.end_datetime ? booking.end_datetime.substring(11, 16) : '11:30';

  bookingForm.value = {
    subject: booking.subject,
    leader: booking.leader,
    meeting_date: dateStr,
    start_time: startTime,
    end_time: endTime,
    participants_count: booking.participants_count,
    preferred_room_id: booking.preferred_room_id || booking.room_id || null,
    required_equipment: Array.isArray(booking.required_equipment) ? [...booking.required_equipment] : [],
    meeting_link: booking.meeting_link || '',
    notes: booking.notes || ''
  };
  showBookingModal.value = true;
};

const closeBookingModal = () => {
  showBookingModal.value = false;
};

const submitBookingForm = async () => {
  if (!bookingForm.value.subject || !bookingForm.value.leader || !bookingForm.value.meeting_date || !bookingForm.value.start_time || !bookingForm.value.end_time) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមបំពេញព័ត៌មានចាំបាច់',
      text: 'សូមបញ្ចូលប្រធានបទ អ្នកដឹកនាំ កាលបរិច្ឆេទ និងម៉ោងចាប់ផ្តើម-បញ្ចប់!',
      confirmButtonText: 'យល់ព្រម'
    });
    return;
  }

  const startDatetime = `${bookingForm.value.meeting_date} ${bookingForm.value.start_time}:00`;
  const endDatetime = `${bookingForm.value.meeting_date} ${bookingForm.value.end_time}:00`;

  if (startDatetime >= endDatetime) {
    Swal.fire({
      icon: 'warning',
      title: 'ម៉ោងមិនត្រឹមត្រូវ',
      text: 'ម៉ោងបញ្ចប់ត្រូវតែនៅក្រោយម៉ោងចាប់ផ្តើម!',
      confirmButtonText: 'យល់ព្រម'
    });
    return;
  }

  savingBooking.value = true;
  try {
    const payload = {
      title: bookingForm.value.subject,
      subject: bookingForm.value.subject,
      leader_name: bookingForm.value.leader,
      leader: bookingForm.value.leader,
      booking_date: bookingForm.value.meeting_date,
      meeting_date: bookingForm.value.meeting_date,
      start_time: bookingForm.value.start_time,
      end_time: bookingForm.value.end_time,
      start_datetime: startDatetime,
      end_datetime: endDatetime,
      participants_count: bookingForm.value.participants_count ? Number(bookingForm.value.participants_count) : 1,
      preferred_room_id: bookingForm.value.preferred_room_id || null,
      required_equipment: bookingForm.value.required_equipment,
      equipment_needed: JSON.stringify(bookingForm.value.required_equipment),
      meeting_link: bookingForm.value.meeting_link || null,
      notes: bookingForm.value.notes || null,
      description: bookingForm.value.notes || null,
    };

    if (isEditingBooking.value && editingBookingId.value) {
      await apiUpdateRoomBooking(editingBookingId.value, payload);
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ',
        text: 'បានកែសម្រួលការស្នើសុំកក់បន្ទប់ដោយជោគជ័យ!',
        timer: 2000,
        showConfirmButton: false
      });
    } else {
      await apiCreateRoomBooking(payload);
      Swal.fire({
        icon: 'success',
        title: 'ស្នើសុំជោគជ័យ',
        text: 'ពាក្យស្នើសុំកក់បន្ទប់ត្រូវបានបញ្ជូនទៅកាន់អ្នកគ្រប់គ្រងដើម្បីពិនិត្យ និងចាត់ចែងបន្ទប់!',
        timer: 2500,
        showConfirmButton: false
      });
    }

    closeBookingModal();
    fetchMyBookings();
    fetchTimetable();
    fetchManageBookings();
  } catch (error) {
    console.error('Booking save failed:', error);
    const msg = error.response?.data?.message || 'មានបញ្ហាក្នុងការរក្សាទុកទិន្នន័យ សូមព្យាយាមម្តងទៀត!';
    Swal.fire({
      icon: 'error',
      title: 'មិនអាចរក្សាទុកបានទេ',
      text: msg,
      confirmButtonText: 'យល់ព្រម'
    });
  } finally {
    savingBooking.value = false;
  }
};

// ==================== CANCEL & DELETE BOOKINGS ====================
const confirmCancelBooking = async (booking) => {
  const result = await Swal.fire({
    title: 'តើលោកអ្នកចង់បោះបង់ការកក់នេះមែនទេ?',
    text: `កិច្ចប្រជុំ៖ "${booking.subject}" នឹងត្រូវបោះបង់ (Cancelled) ហើយបន្ទប់នឹងត្រូវទំនេរឡើងវិញ។`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#f59e0b',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'បាទ/ចាស បោះបង់',
    cancelButtonText: 'មិនបោះបង់'
  });

  if (result.isConfirmed) {
    try {
      await apiCancelRoomBooking(booking.id);
      Swal.fire({
        icon: 'success',
        title: 'បានបោះបង់រួចរាល់',
        text: 'ការកក់បន្ទប់ត្រូវបានបោះបង់ដោយជោគជ័យ!',
        timer: 1800,
        showConfirmButton: false
      });
      fetchMyBookings();
      fetchTimetable();
      fetchManageBookings();
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'បរាជ័យ',
        text: error.response?.data?.message || 'មិនអាចបោះបង់ការកក់បានទេ',
        confirmButtonText: 'យល់ព្រម'
      });
    }
  }
};

const confirmDeleteBooking = async (booking) => {
  const result = await Swal.fire({
    title: 'តើលោកអ្នកពិតជាចង់លុបមែនទេ?',
    text: `កំណត់ត្រាកក់ "${booking.subject}" នឹងត្រូវលុបចេញពីប្រព័ន្ធទាំងស្រុង។`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'បាទ/ចាស លុប',
    cancelButtonText: 'បោះបង់'
  });

  if (result.isConfirmed) {
    try {
      await apiDeleteRoomBooking(booking.id);
      Swal.fire({
        icon: 'success',
        title: 'បានលុបជោគជ័យ',
        timer: 1500,
        showConfirmButton: false
      });
      fetchMyBookings();
      fetchTimetable();
      fetchManageBookings();
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'បរាជ័យ',
        text: error.response?.data?.message || 'មិនអាចលុបបានទេ',
        confirmButtonText: 'យល់ព្រម'
      });
    }
  }
};

// ==================== APPROVAL MODAL ACTIONS ====================
const openApprovalModal = async (booking) => {
  approvalTargetBooking.value = booking;
  approvalAdminNote.value = booking.admin_note || booking.manager_note || '';
  selectedApprovalRoomId.value = booking.room_id || booking.preferred_room_id || null;
  roomAvailabilityList.value = [];
  showApprovalModal.value = true;

  // Check real-time availability for all rooms for this booking window
  loadingAvailability.value = true;
  try {
    const dateVal = booking.booking_date
      ? (typeof booking.booking_date === 'string' ? booking.booking_date.substring(0, 10) : booking.booking_date)
      : (booking.start_datetime ? booking.start_datetime.substring(0, 10) : '');
    const startTimeVal = booking.start_time
      ? (typeof booking.start_time === 'string' ? booking.start_time.substring(0, 5) : booking.start_time)
      : (booking.start_datetime ? booking.start_datetime.substring(11, 16) : '');
    const endTimeVal = booking.end_time
      ? (typeof booking.end_time === 'string' ? booking.end_time.substring(0, 5) : booking.end_time)
      : (booking.end_datetime ? booking.end_datetime.substring(11, 16) : '');

    const res = await apiCheckRoomAvailability({
      date: dateVal,
      booking_date: dateVal,
      start_time: startTimeVal,
      end_time: endTimeVal,
      start_datetime: booking.start_datetime,
      end_datetime: booking.end_datetime,
      exclude_booking_id: booking.id
    });
    roomAvailabilityList.value = res.data?.data || res.data || [];

    // If preferred/current room is available in the list, keep it selected
    if (selectedApprovalRoomId.value) {
      const selectedRoom = roomAvailabilityList.value.find(r => r.id === selectedApprovalRoomId.value);
      if (!selectedRoom || !selectedRoom.is_available) {
        // Fallback to first available room
        const firstAvail = roomAvailabilityList.value.find(r => r.is_available);
        selectedApprovalRoomId.value = firstAvail ? firstAvail.id : null;
      }
    } else {
      const firstAvail = roomAvailabilityList.value.find(r => r.is_available);
      selectedApprovalRoomId.value = firstAvail ? firstAvail.id : null;
    }
  } catch (error) {
    console.error('Failed to check room availability:', error);
  } finally {
    loadingAvailability.value = false;
  }
};

const closeApprovalModal = () => {
  showApprovalModal.value = false;
  approvalTargetBooking.value = null;
  selectedApprovalRoomId.value = null;
  roomAvailabilityList.value = [];
  approvalAdminNote.value = '';
};

const submitApproval = async () => {
  if (!selectedApprovalRoomId.value) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមជ្រើសរើសបន្ទប់',
      text: 'សូមជ្រើសរើសបន្ទប់ប្រជុំដែលទំនេរដើម្បីចាត់ចែងជូនអ្នកស្នើសុំ!',
      confirmButtonText: 'យល់ព្រម'
    });
    return;
  }

  savingApproval.value = true;
  try {
    await apiApproveRoomBooking(approvalTargetBooking.value.id, {
      room_id: selectedApprovalRoomId.value,
      manager_note: approvalAdminNote.value || null,
      admin_note: approvalAdminNote.value || null
    });

    Swal.fire({
      icon: 'success',
      title: 'បានអនុម័តជោគជ័យ!',
      text: 'កិច្ចប្រជុំត្រូវបានអនុម័ត និងកំណត់បន្ទប់ប្រជុំរួចរាល់។',
      timer: 2000,
      showConfirmButton: false
    });

    closeApprovalModal();
    fetchManageBookings();
    fetchTimetable();
    fetchMyBookings();
  } catch (error) {
    console.error('Approval failed:', error);
    Swal.fire({
      icon: 'error',
      title: 'មិនអាចអនុម័តបានទេ',
      text: error.response?.data?.message || 'មានបញ្ហាក្នុងការអនុម័តបន្ទប់',
      confirmButtonText: 'យល់ព្រម'
    });
  } finally {
    savingApproval.value = false;
  }
};

// ==================== REJECT MODAL ACTIONS ====================
const openRejectModal = (booking) => {
  rejectTargetBooking.value = booking;
  rejectionReason.value = '';
  showRejectModal.value = true;
};

const closeRejectModal = () => {
  showRejectModal.value = false;
  rejectTargetBooking.value = null;
  rejectionReason.value = '';
};

const submitReject = async () => {
  if (!rejectionReason.value.trim()) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមបញ្ចូលមូលហេតុ',
      text: 'សូមបញ្ជាក់មូលហេតុនៃការបដិសេធ ដើម្បីជូនដំណឹងទៅអ្នកស្នើសុំ!',
      confirmButtonText: 'យល់ព្រម'
    });
    return;
  }

  savingReject.value = true;
  try {
    await apiRejectRoomBooking(rejectTargetBooking.value.id, {
      rejection_reason: rejectionReason.value
    });

    Swal.fire({
      icon: 'success',
      title: 'បានបដិសេធរួចរាល់',
      text: 'ពាក្យស្នើសុំត្រូវបានបដិសេធ ហើយអ្នកស្នើសុំអាចមើលឃើញមូលហេតុនេះ។',
      timer: 2000,
      showConfirmButton: false
    });

    closeRejectModal();
    fetchManageBookings();
    fetchTimetable();
    fetchMyBookings();
  } catch (error) {
    console.error('Reject failed:', error);
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ',
      text: error.response?.data?.message || 'មិនអាចបដិសេធបានទេ',
      confirmButtonText: 'យល់ព្រម'
    });
  } finally {
    savingReject.value = false;
  }
};

// ==================== DETAIL MODAL ====================
const openBookingDetailModal = (booking) => {
  selectedBooking.value = booking;
  showDetailModal.value = true;
};

const closeDetailModal = () => {
  showDetailModal.value = false;
  selectedBooking.value = null;
};

// ==================== DAY EVENTS MODAL ====================
const openDayEventsModal = (day) => {
  selectedDay.value = day;
  showDayEventsModal.value = true;
};

const closeDayEventsModal = () => {
  showDayEventsModal.value = false;
  selectedDay.value = null;
};

// ==================== ROOM MANAGEMENT MODAL (Admin) ====================
const openCreateRoomModal = () => {
  isEditingRoom.value = false;
  editingRoomId.value = null;
  roomForm.value = {
    name: '',
    location: '',
    capacity: 20,
    facilities: ['Projector / អេក្រង់បញ្ចាំង', 'Sound System / មីក្រូហ្វូន', 'Air Conditioner', 'High-speed WiFi'],
    color: '#3b82f6',
    status: 'ACTIVE',
    manager_id: null,
    description: ''
  };
  showRoomModal.value = true;
};

const openEditRoomModal = (room) => {
  isEditingRoom.value = true;
  editingRoomId.value = room.id;
  roomForm.value = {
    name: room.name,
    location: room.location,
    capacity: room.capacity,
    facilities: Array.isArray(room.facilities_list) ? [...room.facilities_list] : [],
    color: room.color || '#3b82f6',
    status: room.status || 'ACTIVE',
    manager_id: room.manager_id || null,
    description: room.description || ''
  };
  showRoomModal.value = true;
};

const closeRoomModal = () => {
  showRoomModal.value = false;
};

const submitRoomForm = async () => {
  if (!roomForm.value.name || !roomForm.value.location || !roomForm.value.capacity) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមបំពេញព័ត៌មានចាំបាច់',
      text: 'សូមបញ្ចូលឈ្មោះបន្ទប់ ទីតាំង និងចំណុះមនុស្ស!',
      confirmButtonText: 'យល់ព្រម'
    });
    return;
  }

  savingRoom.value = true;
  try {
    const payload = {
      name: roomForm.value.name,
      location: roomForm.value.location,
      capacity: Number(roomForm.value.capacity),
      facilities: roomForm.value.facilities,
      color: roomForm.value.color,
      status: roomForm.value.status,
      manager_id: roomForm.value.manager_id,
      description: roomForm.value.description
    };

    if (isEditingRoom.value && editingRoomId.value) {
      await apiUpdateMeetingRoom(editingRoomId.value, payload);
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ',
        text: 'បានកែសម្រួលព័ត៌មានបន្ទប់ប្រជុំរួចរាល់!',
        timer: 1800,
        showConfirmButton: false
      });
    } else {
      await apiCreateMeetingRoom(payload);
      Swal.fire({
        icon: 'success',
        title: 'ជោគជ័យ',
        text: 'បានបង្កើតបន្ទប់ប្រជុំថ្មីរួចរាល់!',
        timer: 1800,
        showConfirmButton: false
      });
    }

    closeRoomModal();
    fetchRooms();
  } catch (error) {
    console.error('Room save error:', error);
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ',
      text: error.response?.data?.message || 'មិនអាចរក្សាទុកបន្ទប់បានទេ',
      confirmButtonText: 'យល់ព្រម'
    });
  } finally {
    savingRoom.value = false;
  }
};

const confirmDeleteRoom = async (room) => {
  const result = await Swal.fire({
    title: 'តើលោកអ្នកពិតជាចង់លុបបន្ទប់នេះមែនទេ?',
    text: `បន្ទប់ "${room.name}" នឹងត្រូវលុប។ (ប្រសិនបើមានការកក់បន្ទប់នេះ នឹងមិនអនុញ្ញាតឱ្យលុបឡើយ)`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'បាទ/ចាស លុប',
    cancelButtonText: 'បោះបង់'
  });

  if (result.isConfirmed) {
    try {
      await apiDeleteMeetingRoom(room.id);
      Swal.fire({
        icon: 'success',
        title: 'បានលុបជោគជ័យ',
        timer: 1500,
        showConfirmButton: false
      });
      fetchRooms();
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'មិនអាចលុបបានទេ',
        text: error.response?.data?.message || 'មានកំហុសក្នុងការលុបបន្ទប់',
        confirmButtonText: 'យល់ព្រម'
      });
    }
  }
};

// ==================== KHMER FORMATTERS ====================
function toKhmerNum(num) {
  if (num === null || num === undefined) return '';
  const khmerDigits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
  return String(num).replace(/[0-9]/g, (digit) => khmerDigits[digit]);
}

function formatKhmerMonthYear(year, month) {
  const khmerMonths = [
    'មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា',
    'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'
  ];
  return `${khmerMonths[month - 1]} ឆ្នាំ ${toKhmerNum(year)}`;
}

function formatKhmerDate(datetimeStr) {
  if (!datetimeStr) return '-';
  const str = String(datetimeStr);
  const parts = str.substring(0, 10).split('-');
  if (parts.length < 3) return str;
  const year = parseInt(parts[0], 10);
  const month = parseInt(parts[1], 10);
  const day = parseInt(parts[2], 10);
  const khmerMonths = [
    'មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា',
    'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'
  ];
  if (isNaN(day) || isNaN(month) || isNaN(year) || month < 1 || month > 12) {
    return str;
  }
  return `ថ្ងៃទី ${toKhmerNum(day)} ${khmerMonths[month - 1]} ${toKhmerNum(year)}`;
}

function formatTimeOnly(datetimeStr) {
  if (!datetimeStr) return '';
  let timePart = '';
  if (typeof datetimeStr === 'string') {
    if (datetimeStr.includes('T') || datetimeStr.includes(' ')) {
      timePart = datetimeStr.substring(11, 16);
    } else {
      timePart = datetimeStr.substring(0, 5);
    }
  }
  if (!timePart) return '';
  const [h, m] = timePart.split(':');
  if (!h || !m) return timePart;
  return `${toKhmerNum(h)}:${toKhmerNum(m)}`;
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'PENDING':
      return 'badge badge-warning text-dark px-2 py-1';
    case 'APPROVED':
      return 'badge badge-success px-2 py-1';
    case 'REJECTED':
      return 'badge badge-danger px-2 py-1';
    case 'CANCELLED':
      return 'badge badge-secondary px-2 py-1';
    default:
      return 'badge badge-light border px-2 py-1';
  }
}

// Lifecycle
onMounted(async () => {
  await fetchRooms();
  await Promise.all([
    fetchTimetable(),
    fetchMyBookings(),
    fetchManageBookings()
  ]);
});
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}

.font-khmer {
  font-family: 'Kantumruy Pro', 'Hanuman', 'Siemreap', sans-serif;
}

.meeting-room-wrapper {
  background-color: #f8fafc;
}

/* Nav Pills Styling */
.nav-pills .nav-link {
  color: #475569;
  border-radius: 0.375rem;
  padding: 0.5rem 1rem;
}

.nav-pills .nav-link.active {
  background-color: #2563eb;
  color: #ffffff;
}

/* Room Cards */
.room-card {
  transition: transform 0.2s, box-shadow 0.2s;
}

.room-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}

.room-color-bar {
  height: 6px;
  width: 100%;
}

/* Pulse Badge */
.pulse-badge {
  animation: pulse 1.8s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.15); opacity: 0.85; }
  100% { transform: scale(1); opacity: 1; }
}

/* Color Swatches */
.color-swatch {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  cursor: pointer;
  border: 2px solid transparent;
  transition: transform 0.15s, border-color 0.15s;
}

.color-swatch:hover {
  transform: scale(1.15);
}

.color-swatch.active {
  border-color: #0f172a;
  box-shadow: 0 0 0 2px #fff;
  transform: scale(1.15);
}

/* Calendar Grid */
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  background-color: #e2e8f0;
  gap: 1px;
}

.calendar-header-cell {
  font-size: 0.88rem;
}

.calendar-day-cell {
  background-color: #ffffff;
  min-height: 125px;
  padding: 6px;
  display: flex;
  flex-direction: column;
  transition: background-color 0.15s;
}

.calendar-day-cell:hover {
  background-color: #f8fafc;
}

.calendar-day-cell.other-month {
  background-color: #f8fafc;
  opacity: 0.65;
}

.calendar-day-cell.today-cell {
  background-color: #eff6ff;
}

.calendar-day-cell.weekend-cell {
  background-color: #fdfdfd;
}

.day-number {
  font-size: 0.88rem;
}

.today-badge {
  background-color: #2563eb;
  color: #ffffff !important;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.day-events-container {
  display: flex;
  flex-direction: column;
  gap: 2px;
  overflow: hidden;
}

.event-chip {
  font-size: 0.78rem;
  line-height: 1.25;
  transition: opacity 0.15s;
}

.event-chip:hover {
  opacity: 0.85;
}

.more-events-chip {
  background-color: #f1f5f9;
  border-radius: 3px;
  padding: 2px 4px;
}

/* Room Selection Cards in Approval Modal */
.room-select-card {
  border-radius: 0.5rem;
  transition: all 0.2s;
}

.room-select-card.selected {
  background-color: #eff6ff;
  border-color: #2563eb !important;
  box-shadow: 0 0 0 1px #2563eb;
}

.bg-primary-soft {
  background-color: #eff6ff;
}

.bg-danger-soft {
  background-color: #fef2f2;
}

/* Custom Modal Backdrop & Sticky Layout */
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

.text-pre-wrap {
  white-space: pre-wrap;
}

@media (max-width: 768px) {
  .calendar-day-cell {
    min-height: 85px;
    padding: 3px;
  }
  .event-chip {
    font-size: 0.7rem;
    padding: 2px;
  }
}
</style>

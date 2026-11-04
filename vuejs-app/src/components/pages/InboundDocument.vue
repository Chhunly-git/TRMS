<template>
  <div class="content-wrapper khmer-layout">
    <!-- 1. Header Section -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-3 align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0 font-weight-bold text-dark khmer-page-title">
              <i class="fas fa-file-import text-success mr-2"></i>ឯកសារចូល (ន.ប.ធ.)
            </h1>
            <p class="text-muted small mb-0 mt-1">
              តាមដាន និងចាត់ចែងឯកសារចូល
            </p>
          </div>
          <div class="col-sm-6 text-right">
            <button
              class="btn btn-outline-info shadow-sm font-khmer mr-2"
              @click="openTelegramModal"
              title="កំណត់ និងតេស្តការជូនដំណឹងតាម Telegram Bot"
            >
              <i class="fab fa-telegram-plane mr-1"></i> ការកំណត់ Telegram
            </button>
            <button
              v-if="canRegister"
              class="btn btn-dark-custom shadow-sm font-khmer"
              @click="openCreateModal"
            >
              <i class="fas fa-plus-circle mr-1"></i> ចុះលេខឯកសារចូលថ្មី
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- 2. Main Content -->
    <section class="content">
      <div class="container-fluid">

        <!-- 2.1 Stats Overview Cards -->
        <div class="row mb-3">
          <!-- Card 1: ឯកសារសរុប (All Documents) -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div
              class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
              :class="{ 'active-stat-card border-secondary': activeTab === 'all' && !filters.status && !filters.deadline_filter }"
              @click="switchTab('all')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">
                    {{ isRegularOfficer ? 'ឯកសារចូលសរុប' : 'ឯកសារចូលសរុប' }}
                  </span>
                  <h3 class="font-weight-bold mb-0 text-dark">{{ stats.total || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-secondary-subtle text-secondary">
                  <i class="fas fa-folder-open fa-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 2: Pending (ករណីចូលមកដំបូង) -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div
              class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
              :class="{ 'active-stat-card border-warning': activeTab === 'pending' || filters.status === 'pending' }"
              @click="switchTab('pending')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">រងចាំ</span>
                  <h3 class="font-weight-bold mb-0 text-warning">{{ stats.pending || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-warning-light text-warning">
                  <i class="fas fa-clock fa-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 3: Doing (ករណីបានបញ្ជូនចេញ ឬកំពុងធ្វើ) -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div
              class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
              :class="{ 'active-stat-card border-primary': activeTab === 'doing' || filters.status === 'doing' }"
              @click="switchTab('doing')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">កំពុងដំណើរការ</span>
                  <h3 class="font-weight-bold mb-0 text-primary">{{ stats.doing || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-primary-light text-primary">
                  <i class="fas fa-tasks fa-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 4: Complete (ករណីអគ្គនាយកឯកភាព) -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div
              class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
              :class="{ 'active-stat-card border-success': activeTab === 'completed' || filters.status === 'completed' }"
              @click="switchTab('completed')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">បញ្ចប់</span>
                  <h3 class="font-weight-bold mb-0 text-success">{{ stats.completed || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-success-light text-success">
                  <i class="fas fa-check-circle fa-lg"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Secondary Stats Row with My Unit and Deadline Counters -->
        <div class="row mb-3">
          <!-- Card: អង្គភាពរបស់ខ្ញុំ (My Unit) -->
          <div class="col-xl-4 col-md-4 col-sm-6 mb-2" v-if="userStore.department_id || userStore.office_id">
            <div
              class="stat-mini-card stat-mini-teal shadow-sm h-100 cursor-pointer"
              :class="{ 'is-active': activeTab === 'my_unit' }"
              @click="switchTab('my_unit')"
              title="ចុចដើម្បីបង្ហាញឯកសារក្នុងអង្គភាពរបស់ខ្ញុំ"
            >
              <div class="stat-mini-content">
                <span class="stat-mini-title">អង្គភាពរបស់ខ្ញុំ</span>
                <div class="d-flex align-items-baseline mt-1">
                  <span class="stat-mini-number text-teal">{{ stats.my_unit || 0 }}</span>
                  <span class="stat-mini-tag ml-2 text-teal">នាយកដ្ឋាន/ការិយាល័យ</span>
                </div>
              </div>
              <div class="stat-mini-icon bg-teal-subtle text-teal">
                <i class="fas fa-sitemap"></i>
              </div>
              <div class="stat-mini-bar bg-teal"></div>
            </div>
          </div>

          <!-- Card: ជិតដល់កាលកំណត់ (Due Soon <= 3 days) -->
          <div :class="(userStore.department_id || userStore.office_id) ? 'col-xl-4 col-md-4 col-sm-6 mb-2' : 'col-xl-6 col-md-6 col-sm-6 mb-2'">
            <div
              class="stat-mini-card stat-mini-amber shadow-sm h-100 cursor-pointer"
              :class="{ 'is-active': filters.deadline_filter === 'due_soon' }"
              @click="toggleDeadlineFilter('due_soon')"
              title="ចុចដើម្បីបង្ហាញឯកសារជិតផុតកំណត់ <= ៣ ថ្ងៃ"
            >
              <div class="stat-mini-content">
                <span class="stat-mini-title">ជិតដល់កាលកំណត់</span>
                <div class="d-flex align-items-baseline mt-1">
                  <span class="stat-mini-number text-amber">{{ stats.due_soon || 0 }}</span>
                  <span class="stat-mini-tag ml-2 text-amber">&le; ៣ ថ្ងៃ</span>
                </div>
              </div>
              <div class="stat-mini-icon bg-amber-subtle text-amber">
                <i class="fas fa-stopwatch"></i>
              </div>
              <div class="stat-mini-bar bg-amber"></div>
            </div>
          </div>

          <!-- Card: ហួសកាលកំណត់ (Overdue) -->
          <div :class="(userStore.department_id || userStore.office_id) ? 'col-xl-4 col-md-4 col-sm-6 mb-2' : 'col-xl-6 col-md-6 col-sm-6 mb-2'">
            <div
              class="stat-mini-card stat-mini-red shadow-sm h-100 cursor-pointer"
              :class="{ 'is-active': filters.deadline_filter === 'overdue' }"
              @click="toggleDeadlineFilter('overdue')"
              title="ចុចដើម្បីបង្ហាញឯកសារដែលបានហួសកាលកំណត់"
            >
              <div class="stat-mini-content">
                <span class="stat-mini-title">ហួសកាលកំណត់</span>
                <div class="d-flex align-items-baseline mt-1">
                  <span class="stat-mini-number text-red">{{ stats.overdue || 0 }}</span>
                  <span class="stat-mini-tag ml-2 text-red" :class="{ 'pulse-badge': stats.overdue > 0 }">បន្ទាន់</span>
                </div>
              </div>
              <div class="stat-mini-icon bg-red-subtle text-red">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
              <div class="stat-mini-bar bg-red"></div>
            </div>
          </div>
        </div>

        <!-- 2.2 Navigation Tabs & Search/Filter Box -->
        <div class="card shadow-sm border-0 rounded-lg mb-3">
          <div class="card-header bg-white p-0 border-bottom">
            <ul class="nav nav-tabs border-0 px-3 pt-2 align-items-center">
              <!-- Tab 1: ឯកសារទាំងអស់ (All) / ឯកសារទទួលបានទាំងអស់ -->
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'all' }"
                  href="javascript:void(0)"
                  @click="switchTab('all')"
                >
                  <i class="fas fa-th-list text-secondary mr-1"></i> {{ isRegularOfficer ? 'ឯកសារចូលទាំងអស់' : 'ឯកសារទាំងអស់' }}
                  <span class="badge badge-light ml-1">{{ stats.total || 0 }}</span>
                </a>
              </li>

              <!-- Tab 2: Pending (ករណីចូលមកដំបូង) -->
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'pending' }"
                  href="javascript:void(0)"
                  @click="switchTab('pending')"
                >
                  <i class="fas fa-clock text-warning mr-1"></i> រងចាំ
                  <span class="badge badge-warning ml-1" v-if="stats.pending > 0">{{ stats.pending }}</span>
                </a>
              </li>

              <!-- Tab 3: Doing (ករណីបានបញ្ជូនចេញ ឬកំពុងធ្វើ) -->
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'doing' }"
                  href="javascript:void(0)"
                  @click="switchTab('doing')"
                >
                  <i class="fas fa-tasks text-primary mr-1"></i> កំពុងដំណើរការ
                  <span class="badge badge-primary ml-1" v-if="stats.doing > 0">{{ stats.doing }}</span>
                </a>
              </li>

              <!-- Tab 4: Complete (ករណីអគ្គនាយកឯកភាព) -->
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'completed' }"
                  href="javascript:void(0)"
                  @click="switchTab('completed')"
                >
                  <i class="fas fa-check-circle text-success mr-1"></i> បញ្ចប់
                  <span class="badge badge-success ml-1" v-if="stats.completed > 0">{{ stats.completed }}</span>
                </a>
              </li>

              <!-- Tab 5: អង្គភាពរបស់ខ្ញុំ (My Unit) - For Department / Office leaders & members -->
              <li class="nav-item" v-if="userStore.department_id || userStore.office_id">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'my_unit' }"
                  href="javascript:void(0)"
                  @click="switchTab('my_unit')"
                >
                  <i class="fas fa-sitemap text-info mr-1"></i> អង្គភាពរបស់ខ្ញុំ
                  <span class="badge badge-info ml-1" v-if="stats.my_unit > 0">{{ stats.my_unit }}</span>
                </a>
              </li>
            </ul>
          </div>

          <!-- Filter & Search Toolbar -->
          <div class="card-body p-3 bg-light-subtle">
            <div class="row align-items-center">
              <!-- Search Box -->
              <div class="col-lg-2 col-md-6 mb-2">
                <div class="input-group input-group-sm">
                  <div class="input-group-prepend">
                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                  </div>
                  <input
                    type="text"
                    class="form-control border-left-0 font-khmer"
                    placeholder="ស្វែងរកលេខចូល, លេខដើម, កម្មវត្ថុ..."
                    v-model="filters.search"
                    @keyup.enter="loadDocuments"
                  />
                </div>
              </div>

              <!-- 3-State Status Filter (Pending, Doing, Complete) -->
              <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                <select class="form-control form-control-sm font-khmer font-weight-bold" v-model="filters.status" @change="loadDocuments">
                  <option value="">-- ស្ថានភាព (ទាំងអស់) --</option>
                  <option value="pending">🟡 រងចាំ</option>
                  <option value="doing">🔵 កំពុងដំណើរការ</option>
                  <option value="completed">🟢 បញ្ចប់</option>
                </select>
              </div>

              <!-- Urgency Filter -->
              <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                <select class="form-control form-control-sm font-khmer" v-model="filters.urgency" @change="loadDocuments">
                  <option value="">-- កម្រិតបន្ទាន់ --</option>
                  <option value="NORMAL">ធម្មតា</option>
                  <option value="MEDIUM">មធ្យម</option>
                  <option value="URGENT">បន្ទាន់</option>
                  <option value="MOST_URGENT">បន្ទាន់បំផុត</option>
                </select>
              </div>

              <!-- Deadline Filter -->
              <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                <select class="form-control form-control-sm font-khmer" v-model="filters.deadline_filter" @change="loadDocuments">
                  <option value="">-- កាលកំណត់ (Deadline) --</option>
                  <option value="due_soon">ជិតផុតកំណត់ (<= ៣ ថ្ងៃ)</option>
                  <option value="overdue">ហួសកាលកំណត់ (Overdue)</option>
                </select>
              </div>

              <!-- Date From -->
              <div class="col-lg-1 col-md-3 col-sm-6 mb-2">
                <input
                  type="date"
                  class="form-control form-control-sm font-khmer px-1"
                  v-model="filters.from_date"
                  @change="loadDocuments"
                  title="ពីកាលបរិច្ឆេទចូល"
                />
              </div>

              <!-- Date To -->
              <div class="col-lg-1 col-md-3 col-sm-6 mb-2">
                <input
                  type="date"
                  class="form-control form-control-sm font-khmer px-1"
                  v-model="filters.to_date"
                  @change="loadDocuments"
                  title="ដល់កាលបរិច្ឆេទចូល"
                />
              </div>

              <!-- Action buttons -->
              <div class="col-lg-1 col-md-6 col-sm-12 mb-2 text-right">
                <button class="btn btn-sm btn-outline-secondary font-khmer w-100" @click="resetFilters" title="កំណត់ឡើងវិញ">
                  <i class="fas fa-undo"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 2.3 Documents Table -->
        <div class="card shadow-sm border-0 rounded-lg">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="thead-light font-khmer">
                  <tr>
                    <th style="width: 120px;">លេខឯកសារ</th>
                    <th style="min-width: 140px;">កាលបរិច្ឆេទ</th>
                    <th style="min-width: 160px;">ប្រភព</th>
                    <th>កម្មវត្ថុ</th>
                    <th style="min-width: 175px;">ស្ថានភាព</th>
                    <th style="width: 150px;" class="text-center">សកម្មភាព</th>
                  </tr>
                </thead>
                <tbody class="font-khmer">
                  <!-- Loading State -->
                  <tr v-if="loading">
                    <td colspan="6" class="text-center py-5 text-muted">
                      <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>
                      កំពុងទាញយកទិន្នន័យឯកសារ...
                    </td>
                  </tr>

                  <!-- Empty State -->
                  <tr v-else-if="!documents.data || documents.data.length === 0">
                    <td colspan="6" class="text-center py-5 text-muted">
                      <i class="fas fa-folder-open fa-3x mb-2 text-secondary d-block"></i>
                      មិនមានទិន្នន័យឯកសារចូលនៅក្នុងប្រព័ន្ធឡើយ
                    </td>
                  </tr>

                  <!-- Data Rows -->
                  <tr v-for="doc in documents.data" :key="doc.id">
                    <!-- 1. លេខឯកសារ -->
                    <td>
                      <span class="badge badge-dark font-13 px-2 py-1 shadow-2xs font-monospace">
                        {{ doc.general_inbound_number || '---' }}
                      </span>
                    </td>

                    <!-- 3. កាលបរិច្ឆេទ -->
                    <td>
                      <div class="font-weight-bold font-13 text-dark">
                        <i class="far fa-calendar-alt text-muted mr-1"></i>
                        {{ formatDateKh(doc.received_date) }}
                      </div>
                      <div class="small text-muted">
                        <i class="far fa-clock mr-1"></i>{{ doc.received_time ? doc.received_time.substring(0, 5) : '' }}
                      </div>
                      
                    </td>

                    <!-- 4. មកពីអង្គភាព & លេខលិខិតដើម -->
                    <td>
                      <div class="font-weight-bold text-dark font-13">
                        <i class="fas fa-building text-secondary mr-1"></i>
                        {{ doc.sender_organization }}
                      </div>
                      <div class="small text-secondary mt-1">
                        <i class="far fa-user mr-1"></i>{{ doc.deliverer_name }}
                        <span v-if="doc.deliverer_phone">({{ doc.deliverer_phone }})</span>
                      </div>
                      <div class="small text-muted mt-1" v-if="doc.external_reference_number">
                        លេខដើម៖ <span class="font-weight-bold">{{ doc.external_reference_number }}</span>
                      </div>
                    </td>

                    <!-- 5. កម្មវត្ថុ & Tags -->
                    <td>
                      <div class="font-weight-bold font-13 mb-1 text-primary cursor-pointer hover-underline" @click="openDetailModal(doc)">
                        {{ doc.title }}
                      </div>
                      <div class="d-flex flex-wrap gap-1 align-items-center">
                        <!-- Urgency -->
                        <span
                          v-if="doc.urgency === 'URGENT' || doc.urgency === 'MOST_URGENT'"
                          class="badge font-11"
                          :class="doc.urgency === 'MOST_URGENT' ? 'badge-danger' : 'badge-warning text-dark'"
                        >
                          <i class="fas fa-bolt mr-1"></i>{{ doc.urgency_kh }}
                        </span>

                        <!-- Response Requirement Tag -->
                        <span
                          v-if="doc.is_response_required"
                          class="badge badge-warning text-dark font-11"
                          title="ឯកសារតម្រូវឱ្យមានលិខិតឆ្លើយតបត្រឡប់ទៅវិញ"
                        >
                          <i class="fas fa-reply mr-1"></i>ត្រូវឆ្លើយតប
                        </span>

                        <!-- Deadline Tag -->
                        <span
                          v-if="doc.deadline"
                          class="badge font-11 shadow-2xs"
                          :class="{
                            'badge-danger': doc.deadline_status === 'OVERDUE' || doc.deadline_status === 'TODAY',
                            'badge-warning text-dark': doc.deadline_status === 'DUE_SOON',
                            'badge-info': doc.deadline_status === 'ON_TRACK',
                            'badge-light border text-muted': doc.deadline_status === 'RESOLVED'
                          }"
                          :title="`កាលកំណត់៖ ${formatDateKh(doc.deadline)}`"
                        >
                          <i class="fas mr-1" :class="doc.deadline_status === 'OVERDUE' ? 'fa-exclamation-triangle' : (doc.deadline_status === 'RESOLVED' ? 'fa-check' : 'fa-clock')"></i>
                          {{ doc.deadline_remaining_kh || formatDateKh(doc.deadline) }} ({{ formatDateKh(doc.deadline) }})
                        </span>
                      </div>
                    </td>

                    <!-- 5. ស្ថានភាព & អ្នកទទួលបន្ទុក -->
                    <td>
                      <div class="d-flex align-items-center">
                        <!-- Profile Image of Custodian (អ្នកកាន់ឯកសារបច្ចុប្បន្ន) -->
                        <div class="mr-2 flex-shrink-0" v-if="getCurrentCustodianProfiles(doc).length > 0">
                          <div class="d-flex align-items-center">
                            <div
                              v-for="(custodian, cIdx) in getCurrentCustodianProfiles(doc)"
                              :key="cIdx"
                              class="custodian-avatar-wrapper position-relative"
                              :title="custodian.title"
                            >
                              <img
                                v-if="custodian.avatarUrl"
                                :src="custodian.avatarUrl"
                                :alt="custodian.name"
                                class="rounded-circle shadow-xs border custodian-avatar"
                                @error="(e) => { e.target.style.display = 'none'; if (e.target.nextElementSibling) e.target.nextElementSibling.style.display = 'flex'; }"
                              />
                              <div
                                class="rounded-circle shadow-xs text-white align-items-center justify-content-center custodian-avatar-fallback font-weight-bold"
                                :style="{ display: custodian.avatarUrl ? 'none' : 'flex' }"
                              >
                                {{ custodian.initial }}
                              </div>
                            </div>
                          </div>
                        </div>

                        <!-- Status Badge & Subtext -->
                        <div>
                          <div class="d-flex align-items-center mb-1">
                            <span class="badge px-2 py-1 font-12 d-inline-flex align-items-center shadow-2xs" :class="getSimplifiedStatus(doc).badgeClass">
                              <i :class="getSimplifiedStatus(doc).icon" class="mr-1"></i>
                              <span>{{ getSimplifiedStatus(doc).label }}</span>
                            </span>
                          </div>
                          <div class="small text-muted font-11 text-nowrap" v-if="getSimplifiedStatus(doc).subText">
                            {{ getSimplifiedStatus(doc).subText }}
                          </div>
                        </div>
                      </div>

                      <!-- ជំនួយការបានពិនិត្យ / មិនទាន់ពិនិត្យ (សម្រាប់ DG / Assistant) -->
                      <div v-if="(isDg || canAssist || isAdmin) && doc.status !== 'COMPLETED'" class="mt-1">
                        <span v-if="doc.assistant_reviewed_at" class="badge badge-success px-1 py-1 font-10">
                          <i class="fas fa-check-circle mr-1"></i>ជំនួយការបានពិនិត្យរួច
                        </span>
                        <span v-else-if="!doc.dispatched_at" class="badge badge-warning px-1 py-1 font-10">
                          <i class="fas fa-clock mr-1"></i>ជំនួយការមិនទាន់ពិនិត្យ
                        </span>
                      </div>
                    </td>

                    <!-- 8. សកម្មភាព -->
                    <td class="text-center">
                      <div class="btn-group btn-group-sm">
                        <!-- View Detail Button -->
                        <button
                          class="btn btn-outline-info"
                          title="មើលព័ត៌មានលម្អិត & ប្រវត្តិលំហូរ"
                          @click="openDetailModal(doc)"
                        >
                          <i class="fas fa-eye"></i>
                        </button>

                        <!-- Tracking Flow Button -->
                        <button
                          class="btn btn-outline-info"
                          title="តាមដានលំហូរឯកសារ (Tracking Flow)"
                          @click="openTrackingModal(doc)"
                        >
                          <i class="fas fa-project-diagram text-info"></i>
                        </button>

                        <!-- Step 1: Send to DG & Assistant (by Receptionist) -->
                        <button
                          v-if="canRegister && doc.status === 'RECEPTION_DRAFT'"
                          class="btn btn-outline-primary"
                          title="បញ្ជូនទៅអគ្គនាយក និងជំនួយការ"
                          @click="sendToAssistantAction(doc)"
                        >
                          <i class="fas fa-paper-plane"></i>
                        </button>

                        <!-- Step 3: DG Annotation & Dispatch (ឯកឧត្តមអគ្គនាយកកត់ត្រាចំណារ & ចាត់ចែងចែកចាយ) -->
                        <button
                          v-if="(isDg || isAdmin) && doc.status === 'SUBMITTED_TO_DG'"
                          class="btn btn-outline-danger"
                          title="កត់ត្រាចំណារ និងចាត់ចែង (ឯកឧត្តមអគ្គនាយក)"
                          @click="openDgAnnotateModal(doc)"
                        >
                          <i class="fas fa-signature"></i>
                        </button>

                        <!-- ជំនួយការអគ្គនាយកចុច «បានពិនិត្យរួចរាល់» -->
                        <button
                          v-if="canAssist && !doc.dispatched_at && !doc.assistant_reviewed_at && doc.status !== 'COMPLETED'"
                          class="btn btn-outline-success"
                          title="ជំនួយការចុច៖ បានពិនិត្យរួចរាល់"
                          @click="markAssistantReviewedAction(doc)"
                        >
                          <i class="fas fa-check"></i>
                        </button>

                        <!-- ទាញយកការចាត់ចែងមកវិញ (Recall) -->
                        <button
                          v-if="canRecallInbound(doc)"
                          class="btn btn-outline-danger"
                          title="ទាញយកការចាត់ចែងមកវិញ (Recall)"
                          @click="recallInboundAction(doc)"
                        >
                          <i class="fas fa-undo"></i>
                        </button>

                        <!-- Step 6B: Process Review Action on Response -->
                        <button
                          v-if="canReviewResponse(doc)"
                          class="btn btn-outline-primary"
                          title="ពិនិត្យ និងចារឆ្លងលិខិតឆ្លើយតប"
                          @click="openReviewResponseModal(doc)"
                        >
                          <i class="fas fa-clipboard-check"></i>
                        </button>

                        <!-- View / Preview PDF Button -->
                        <button
                          v-if="doc.annotated_file_path || doc.original_file_path"
                          class="btn btn-outline-info"
                          :title="doc.annotated_file_path ? 'មើលឯកសារមានចំណារ (PDF)' : 'មើលឯកសារ Scan ដើម (PDF)'"
                          @click="doc.annotated_file_path ? previewAnnotatedDoc(doc) : previewOriginalDoc(doc)"
                        >
                          <i class="fas fa-file-pdf text-danger"></i>
                        </button>

                        <!-- Download Scans Menu / Button -->
                        <button
                          v-if="doc.original_file_path"
                          class="btn btn-outline-secondary"
                          title="ទាញយកឯកសារ Scan ដើម"
                          @click="downloadOriginal(doc)"
                        >
                          <i class="fas fa-download text-secondary"></i>
                        </button>

                        <!-- Edit Button (DG / Receptionist / Assistant / Admin) -->
                        <button
                          v-if="canEditDocument(doc)"
                          class="btn btn-outline-warning text-dark"
                          title="កែប្រែព័ត៌មានឯកសារ (ករណីបញ្ចូលខុស)"
                          @click="openEditModal(doc)"
                        >
                          <i class="fas fa-edit"></i>
                        </button>

                        <!-- Delete Button (DG / Receptionist / Assistant / Admin) -->
                        <button
                          v-if="canDeleteDocument(doc)"
                          class="btn btn-outline-danger"
                          title="លុបឯកសារ (ករណីបញ្ចូលខុស)"
                          @click="deleteDocumentAction(doc)"
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

          <!-- Pagination -->
          <div class="card-footer bg-white py-2" v-if="documents.total > 0">
            <div class="row align-items-center">
              <div class="col-sm-6 text-muted small font-khmer">
                បង្ហាញពី {{ documents.from || 0 }} ដល់ {{ documents.to || 0 }} នៃឯកសារសរុប {{ documents.total || 0 }}
              </div>
              <div class="col-sm-6 text-right">
                <ul class="pagination pagination-sm m-0 justify-content-end font-khmer">
                  <li class="page-item" :class="{ disabled: !documents.prev_page_url }">
                    <a class="page-link" href="javascript:void(0)" @click="changePage(documents.current_page - 1)">« មុន</a>
                  </li>
                  <li
                    v-for="page in paginationPages"
                    :key="page"
                    class="page-item"
                    :class="{ active: page === documents.current_page }"
                  >
                    <a class="page-link" href="javascript:void(0)" @click="changePage(page)">{{ page }}</a>
                  </li>
                  <li class="page-item" :class="{ disabled: !documents.next_page_url }">
                    <a class="page-link" href="javascript:void(0)" @click="changePage(documents.current_page + 1)">បន្ទាប់ »</a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- ==================== MODAL 1: ចុះបញ្ជីឯកសារចូលថ្មី (RECEPTIONIST) ==================== -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showCreateModal">
        <div class="modal-dialog modal-xl modal-dialog-centered my-auto" style="width: 100%; max-width: 1050px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden d-flex flex-column" style="max-height: 90vh;">
            <div class="modal-header bg-dark-custom text-white py-3 px-4 flex-shrink-0 align-items-center">
              <h5 class="modal-title font-khmer font-weight-bold mb-0">
                <i class="fas fa-file-signature text-warning mr-2"></i>ចុះបញ្ជីឯកសារចូលថ្មី (អ្នកទទួលឯកសារ)
              </h5>
              <button type="button" class="close text-white" @click="closeCreateModal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <form id="createInboundDocForm" @submit.prevent="submitCreateDocument" class="d-flex flex-column flex-grow-1 overflow-hidden m-0">
              <div class="modal-body p-4 font-khmer flex-grow-1" style="overflow-y: auto; overflow-x: hidden; max-height: calc(88vh - 130px);">
                <!-- Row 1: លេខចូលទូទៅ (Auto) & កាលបរិច្ឆេទ/ម៉ោង -->
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">លេខចូលទូទៅ <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <input
                        type="text"
                        class="form-control font-weight-bold font-monospace"
                        placeholder="ឧ. 001/26"
                        v-model="createForm.general_inbound_number"
                        required
                      />
                      <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" @click="fetchNextGeneralNumber" title="បង្កើតលេខបន្ទាប់ដោយស្វ័យប្រវត្តិ">
                          <i class="fas fa-sync-alt"></i>
                        </button>
                      </div>
                    </div>
                    <small class="text-muted">អាចវាយបញ្ចូលផ្ទាល់ ឬចុច icon ដើម្បីបង្កើតលេខស្វ័យប្រវត្តិ</small>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">កាលបរិច្ឆេទចូល <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" v-model="createForm.received_date" required />
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">ម៉ោងចូល <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" v-model="createForm.received_time" required />
                  </div>
                </div>

                <!-- Row 2: ព័ត៌មានអ្នកយកមក & មកពីអង្គភាពណា -->
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">ឈ្មោះអ្នកយកមក <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="ឈ្មោះអ្នកប្រគល់ឯកសារ"
                      v-model="createForm.deliverer_name"
                      required
                    />
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">លេខទំនាក់ទំនង</label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="លេខទូរស័ព្ទអ្នកយកមក"
                      v-model="createForm.deliverer_phone"
                    />
                  </div>

                  <div class="col-md-4 mb-3 position-relative">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <label class="form-label font-weight-bold mb-0">មកពីអង្គភាព / ស្ថាប័ន <span class="text-danger">*</span></label>
                      <button
                        type="button"
                        class="btn btn-xs btn-outline-primary"
                        @click="openManageOrgModal"
                        title="គ្រប់គ្រងស្ថាប័ន (បន្ថែម/លុប)"
                      >
                        <i class="fas fa-cog mr-1"></i>គ្រប់គ្រងស្ថាប័ន
                      </button>
                    </div>

                    <!-- Searchable Select Trigger Button -->
                    <div class="input-group">
                      <button
                        type="button"
                        class="form-control text-left d-flex justify-content-between align-items-center bg-white"
                        :class="{ 'border-primary ring-1': isOrgDropdownOpen }"
                        @click="toggleOrgDropdown"
                      >
                        <span class="text-truncate mr-2 font-weight-500 font-14" :class="{ 'text-muted': !senderOrgSelection }">
                          <template v-if="senderOrgSelection === '__OTHER__'">
                            <i class="fas fa-edit text-warning mr-1"></i> ផ្សេងៗ ({{ customSenderOrg || 'វាយបញ្ចូលផ្ទាល់' }})
                          </template>
                          <template v-else-if="senderOrgSelection">
                            <i class="fas fa-building text-primary mr-1"></i> {{ senderOrgSelection }}
                          </template>
                          <template v-else>
                            <i class="fas fa-search text-muted mr-1"></i> -- ជ្រើសរើស ឬស្វែងរកស្ថាប័ន --
                          </template>
                        </span>
                        <i class="fas text-muted font-12" :class="isOrgDropdownOpen ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                      </button>
                      <div class="input-group-append" v-if="senderOrgSelection">
                        <button class="btn btn-outline-secondary" type="button" @click="clearSelectedOrg" title="សម្អាតជម្រើស">
                          <i class="fas fa-times"></i>
                        </button>
                      </div>
                    </div>

                    <!-- Searchable Dropdown Popup -->
                    <div
                      v-if="isOrgDropdownOpen"
                      class="position-absolute shadow-lg bg-white rounded border p-2 mt-1 w-100"
                      style="z-index: 1050; left: 0; right: 0; width: 100%;"
                    >
                      <!-- Search Input -->
                      <div class="input-group input-group-sm mb-2">
                        <div class="input-group-prepend">
                          <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input
                          type="text"
                          class="form-control font-khmer font-13"
                          placeholder="វាយស្វែងរក (ខ្មែរ / Code)..."
                          v-model="orgSearchQuery"
                        />
                        <div class="input-group-append" v-if="orgSearchQuery">
                          <button class="btn btn-light border" type="button" @click="orgSearchQuery = ''">
                            <i class="fas fa-times"></i>
                          </button>
                        </div>
                      </div>

                      <!-- Items List -->
                      <div style="max-height: 220px; overflow-y: auto;">
                        <div
                          v-for="org in filteredSenderOrganizations"
                          :key="org.id"
                          class="px-2 py-1-5 rounded cursor-pointer mb-1 d-flex justify-content-between align-items-center hover-bg-light"
                          :class="{ 'bg-primary text-white': senderOrgSelection === org.name_kh }"
                          @click="selectOrg(org)"
                        >
                          <span class="font-13 text-truncate mr-2">{{ org.name_kh }}</span>
                          <span class="badge" :class="senderOrgSelection === org.name_kh ? 'badge-light text-primary' : 'badge-secondary font-11 font-monospace'">
                            {{ org.code || org.category }}
                          </span>
                        </div>

                        <!-- Other Option -->
                        <div
                          class="px-2 py-1-5 rounded cursor-pointer mt-1 border-top pt-2 d-flex align-items-center hover-bg-light text-primary font-weight-bold font-13"
                          :class="{ 'bg-info-light': senderOrgSelection === '__OTHER__' }"
                          @click="selectOtherOrg"
                        >
                          <i class="fas fa-edit mr-2 text-warning"></i> ផ្សេងៗ (វាយបញ្ចូលដោយផ្ទាល់ដៃ)
                        </div>

                        <!-- Empty Search Result -->
                        <div v-if="filteredSenderOrganizations.length === 0 && orgSearchQuery" class="text-center text-muted py-2 font-13">
                          <div>រកមិនឃើញស្ថាប័នត្រូវគ្នាឡើយ</div>
                          <button type="button" class="btn btn-xs btn-outline-primary mt-1" @click="useQueryAsCustomOrg">
                            <i class="fas fa-check mr-1"></i> ប្រើឈ្មោះ «{{ orgSearchQuery }}»
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- Text Input if OTHER is chosen -->
                    <div class="mt-2" v-if="senderOrgSelection === '__OTHER__'">
                      <input
                        type="text"
                        class="form-control"
                        placeholder="សូមវាយបញ្ចូលឈ្មោះអង្គភាព / ស្ថាប័ន..."
                        v-model="customSenderOrg"
                        @input="handleOrgSelectionChange"
                        required
                      />
                    </div>
                  </div>
                </div>

                <!-- Row 3: លេខលិខិតដើមខាងក្រៅ, កាលបរិច្ឆេទលិខិតដើម & កាលកំណត់ (Deadline) -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">លេខលិខិតដើមខាងក្រៅ (បើមាន)</label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="ឧ. ១២៣ សហវ.អ.ន.ប."
                      v-model="createForm.external_reference_number"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold text-danger">
                      <i class="far fa-calendar-times mr-1"></i>កាលកំណត់ (Deadline បើមាន)
                    </label>
                    <input type="date" class="form-control border-danger-subtle" v-model="createForm.deadline" />
                  </div>
                </div>

                <!-- Row 4: កម្មវត្ថុ -->
                <div class="mb-3">
                  <label class="form-label font-weight-bold">កម្មវត្ថុ / ខ្លឹមសារសង្ខេប <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    rows="3"
                    placeholder="សូមបញ្ជាក់កម្មវត្ថុនៃឯកសារចូល..."
                    v-model="createForm.title"
                    required
                  ></textarea>
                </div>

                <!-- Row 5: កម្រិតបន្ទាន់ -->
                <div class="mb-3">
                  <label class="form-label font-weight-bold">កម្រិតបន្ទាន់</label>
                  <select class="form-control" v-model="createForm.urgency">
                    <option value="NORMAL">ធម្មតា</option>
                    <option value="MEDIUM">មធ្យម</option>
                    <option value="URGENT">បន្ទាន់</option>
                    <option value="MOST_URGENT">បន្ទាន់បំផុត</option>
                  </select>
                </div>

                <!-- Row 6: Upload Scan ឯកសារដើម -->
                <div class="mb-3">
                  <label class="form-label font-weight-bold">
                    <i class="fas fa-file-upload text-primary mr-1"></i>Scan ឯកសារដើម (PDF / រូបភាព)
                  </label>
                  <div class="custom-file">
                    <input
                      type="file"
                      class="custom-file-input"
                      id="originalFileInput"
                      @change="handleOriginalFileChange"
                      accept=".pdf,.jpg,.jpeg,.png"
                    />
                    <label class="custom-file-label" for="originalFileInput">
                      {{ selectedOriginalFileName || 'ជ្រើសរើសឯកសារ Scan...' }}
                    </label>
                  </div>
                </div>

              </div>

              <!-- Pinned Footer at bottom of dialog -->
              <div class="modal-footer bg-light py-2 px-4 border-top flex-shrink-0 font-khmer justify-content-between">
                <button type="button" class="btn btn-secondary px-3" @click="closeCreateModal">
                  <i class="fas fa-times mr-1"></i> បោះបង់
                </button>
                <button type="submit" form="createInboundDocForm" class="btn btn-primary px-4 shadow-sm font-weight-bold" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                  <i class="fas fa-save mr-1" v-else></i> រក្សាទុក
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ==================== MODAL 1B: កែប្រែព័ត៌មានឯកសារចូល (Edit Inbound Document Modal) ==================== -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showEditModal && selectedEditDoc">
        <div class="modal-dialog modal-xl modal-dialog-centered my-auto" style="width: 100%; max-width: 1050px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden d-flex flex-column" style="max-height: 90vh;">
            <div class="modal-header bg-dark-custom text-white py-3 px-4 flex-shrink-0 align-items-center">
              <h5 class="modal-title font-khmer font-weight-bold mb-0">
                <i class="fas fa-edit text-warning mr-2"></i>កែប្រែព័ត៌មានឯកសារចូល
                <span class="badge badge-warning text-dark ml-2 font-monospace">{{ selectedEditDoc.general_inbound_number }}</span>
              </h5>
              <button type="button" class="close text-white" @click="closeEditModal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>

            <form id="editInboundDocForm" @submit.prevent="submitEditDocument" class="d-flex flex-column flex-grow-1 overflow-hidden m-0">
              <div class="modal-body p-4 font-khmer flex-grow-1" style="overflow-y: auto; overflow-x: hidden; max-height: calc(88vh - 130px);">
                <!-- Row 1: លេខចូលទូទៅ & កាលបរិច្ឆេទ/ម៉ោង -->
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">លេខចូលទូទៅ <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control font-weight-bold font-monospace"
                      placeholder="ឧ. 001/26"
                      v-model="editForm.general_inbound_number"
                      required
                    />
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">កាលបរិច្ឆេទចូល <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" v-model="editForm.received_date" required />
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">ម៉ោងចូល <span class="text-danger">*</span></label>
                    <input type="time" class="form-control" v-model="editForm.received_time" required />
                  </div>
                </div>

                <!-- Row 2: ព័ត៌មានអ្នកយកមក & មកពីអង្គភាពណា -->
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">ឈ្មោះអ្នកយកមក <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="ឈ្មោះអ្នកប្រគល់ឯកសារ"
                      v-model="editForm.deliverer_name"
                      required
                    />
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">លេខទំនាក់ទំនង</label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="លេខទូរស័ព្ទអ្នកយកមក"
                      v-model="editForm.deliverer_phone"
                    />
                  </div>

                  <div class="col-md-4 mb-3 position-relative">
                    <label class="form-label font-weight-bold">មកពីក្រសួង/ស្ថាប័ន/ក្រុមហ៊ុន <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="វាយបញ្ចូលឈ្មោះអង្គភាព..."
                      v-model="editForm.sender_organization"
                      required
                    />
                  </div>
                </div>

                <!-- Row 3: លេខលិខិតដើម, កាលបរិច្ឆេទលិខិតដើម, កាលកំណត់ -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">លេខលិខិតដើម (ខាងក្រៅ)</label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="ឧ. ១២៣ សជណ"
                      v-model="editForm.external_reference_number"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold text-danger">កាលកំណត់ឆ្លើយតប (Deadline)</label>
                    <input type="date" class="form-control border-danger" v-model="editForm.deadline" />
                  </div>
                </div>

                <!-- Row 4: កម្មវត្ថុ -->
                <div class="mb-3">
                  <label class="form-label font-weight-bold">កម្មវត្ថុ / ខ្លឹមសារសង្ខេប <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    rows="3"
                    placeholder="សូមបញ្ជាក់កម្មវត្ថុនៃឯកសារចូល..."
                    v-model="editForm.title"
                    required
                  ></textarea>
                </div>

                <!-- Row 5: កម្រិតបន្ទាន់ -->
                <div class="mb-3">
                  <label class="form-label font-weight-bold">កម្រិតបន្ទាន់</label>
                  <select class="form-control" v-model="editForm.urgency">
                    <option value="NORMAL">ធម្មតា</option>
                    <option value="MEDIUM">មធ្យម</option>
                    <option value="URGENT">បន្ទាន់</option>
                    <option value="MOST_URGENT">បន្ទាន់បំផុត</option>
                  </select>
                </div>

                <!-- Row 6: កំណត់សម្គាល់ & ឯកសារ Scan -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">កំណត់សម្គាល់បន្ថែម</label>
                    <textarea
                      class="form-control"
                      rows="3"
                      placeholder="កំណត់សម្គាល់ផ្សេងៗ..."
                      v-model="editForm.receptionist_notes"
                    ></textarea>
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="form-label font-weight-bold">ឯកសារ Scan ដើម (PDF/Image)</label>
                    <div class="custom-file mb-1">
                      <input
                        type="file"
                        class="custom-file-input"
                        id="editDocFileInput"
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                        @change="onEditFileChange"
                      />
                      <label class="custom-file-label font-khmer text-truncate" for="editDocFileInput">
                        {{ editForm.original_file ? editForm.original_file.name : (selectedEditDoc.original_file_name || 'ជ្រើសរើសឯកសារថ្មីដើម្បីជំនួស...') }}
                      </label>
                    </div>
                    <small class="text-muted d-block" v-if="selectedEditDoc.original_file_name">
                      ឯកសារបច្ចុប្បន្ន៖ <a href="javascript:void(0)" @click="previewOriginalDoc(selectedEditDoc)">{{ selectedEditDoc.original_file_name }}</a>
                    </small>
                  </div>
                </div>
              </div>

              <!-- Pinned Footer -->
              <div class="modal-footer bg-light py-2 px-4 border-top flex-shrink-0 font-khmer justify-content-between">
                <button type="button" class="btn btn-secondary px-3" @click="closeEditModal">
                  <i class="fas fa-times mr-1"></i> បោះបង់
                </button>
                <button type="submit" form="editInboundDocForm" class="btn btn-warning text-dark px-4 shadow-sm font-weight-bold" :disabled="editingDoc">
                  <span v-if="editingDoc" class="spinner-border spinner-border-sm mr-1"></span>
                  <i class="fas fa-save mr-1" v-else></i> រក្សាទុកការកែប្រែ
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>
    <!-- ==================== MODAL 3: ចំណារឯកឧត្តមអគ្គនាយក (DG ANNOTATION) ==================== -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showDgAnnotateModal">
        <div class="modal-dialog modal-lg modal-dialog-centered my-auto" style="width: 100%; max-width: 800px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden">
            <div class="modal-header bg-danger text-white py-3 px-4 flex-shrink-0 align-items-center">
              <h5 class="modal-title font-khmer font-weight-bold mb-0">
                <i class="fas fa-pen-nib mr-2"></i>ចំណារឯកឧត្តមអគ្គនាយក
              </h5>
              <button type="button" class="close text-white" @click="showDgAnnotateModal = false">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body p-4 font-khmer" style="max-height: calc(85vh - 120px); overflow-y: auto;">
              <div class="alert alert-secondary py-2 font-13 mb-3">
                លេខឯកសារ៖ <strong class="text-primary">{{ selectedDoc?.general_inbound_number }}</strong><br />
                កម្មវត្ថុ៖ {{ selectedDoc?.title }}
              </div>

              <form @submit.prevent="submitDgAnnotate">
                <!-- ខ្លឹមសារចំណារ -->
                <div class="mb-3">
                  <label class="form-label font-weight-bold">ខ្លឹមសារចំណាររបស់ឯកឧត្តមអគ្គនាយក <span class="text-danger">*</span></label>
                  <textarea
                    class="form-control"
                    rows="3"
                    placeholder="សូមកត់ត្រាខ្លឹមសារចំណារ..."
                    v-model="dgAnnotateForm.dg_annotation"
                    required
                  ></textarea>
                </div>

                <!-- កាលបរិច្ឆេទកំណត់ / Deadline -->
                <div class="mb-3">
                  <label class="form-label font-weight-bold text-danger">
                    <i class="far fa-calendar-times mr-1"></i>កាលបរិច្ឆេទកំណត់ / Deadline (បើមាន)
                  </label>
                  <input
                    type="date"
                    class="form-control border-danger-subtle font-weight-bold"
                    v-model="dgAnnotateForm.deadline"
                  />
                  <small class="text-muted">កំណត់កាលបរិច្ឆេទដែលត្រូវចាត់ចែង ឬឆ្លើយតបឱ្យបានរួចរាល់ (បើមាន)</small>
                </div>

                <!-- ជ្រើសរើសភាគីពាក់ព័ន្ធដើម្បីចាត់ចែង (នាយកដ្ឋាន, ការិយាល័យ, មន្ត្រី, ក្រុមការងារ) -->
                <div class="mb-3">
                  <div class="card border rounded-lg p-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <label class="form-label font-weight-bold text-dark m-0">
                        <i class="fas fa-sitemap text-danger mr-1"></i> ចំណារចាត់ចែងទៅកាន់ភាគីពាក់ព័ន្ធ (ជ្រើសរើសដើម្បីចាត់ចែង)
                      </label>
                      <span class="badge badge-dark">
                        សរុប {{ dgAnnotateForm.department_ids.length + dgAnnotateForm.office_ids.length + dgAnnotateForm.user_ids.length + (dgAnnotateForm.working_group_ids ? dgAnnotateForm.working_group_ids.length : 0) }} គោលដៅ
                      </span>
                    </div>
                    <small class="text-muted d-block mb-3">
                      * ឯកឧត្តមអគ្គនាយកអាចជ្រើសរើសគោលដៅចាត់ចែងភ្លាមៗ (នាយកដ្ឋាន, ការិយាល័យ, មន្ត្រី ឬក្រុមការងារ)។
                    </small>

                    <!-- Section 1: Departments -->
                    <div class="mb-3 bg-white p-2 rounded border">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="font-13 text-primary"><i class="fas fa-building mr-1"></i> ១. នាយកដ្ឋានគោលដៅ៖</strong>
                        <span class="badge badge-primary">{{ dgAnnotateForm.department_ids.length }} បានជ្រើស</span>
                      </div>
                      <div class="d-flex flex-wrap gap-2" style="max-height: 120px; overflow-y: auto;">
                        <label
                          v-for="d in recipientOptions.departments"
                          :key="'dg_dept_' + d.id"
                          class="btn btn-xs mr-1 mb-1 font-khmer cursor-pointer border"
                          :class="dgAnnotateForm.department_ids.includes(d.id) ? 'btn-primary text-white' : 'btn-light text-dark'"
                        >
                          <input
                            type="checkbox"
                            class="d-none"
                            :value="d.id"
                            v-model="dgAnnotateForm.department_ids"
                            @change="syncDgTargetsToAnnotation"
                          />
                          <i class="fas" :class="dgAnnotateForm.department_ids.includes(d.id) ? 'fa-check-circle mr-1' : 'fa-plus-circle text-muted mr-1'"></i>
                          {{ d.name_kh }}
                        </label>
                      </div>
                    </div>

                    <!-- Section 2: Offices -->
                    <div class="mb-3 bg-white p-2 rounded border">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="font-13 text-info"><i class="fas fa-door-closed mr-1"></i> ២. ការិយាល័យគោលដៅ៖</strong>
                        <span class="badge badge-info">{{ dgAnnotateForm.office_ids.length }} បានជ្រើស</span>
                      </div>
                      <div class="d-flex flex-wrap gap-2" style="max-height: 130px; overflow-y: auto;">
                        <label
                          v-for="o in recipientOptions.offices"
                          :key="'dg_off_' + o.id"
                          class="btn btn-xs mr-1 mb-1 font-khmer cursor-pointer border"
                          :class="dgAnnotateForm.office_ids.includes(o.id) ? 'btn-info text-white' : 'btn-light text-dark'"
                        >
                          <input
                            type="checkbox"
                            class="d-none"
                            :value="o.id"
                            v-model="dgAnnotateForm.office_ids"
                            @change="syncDgTargetsToAnnotation"
                          />
                          <i class="fas" :class="dgAnnotateForm.office_ids.includes(o.id) ? 'fa-check-circle mr-1' : 'fa-plus-circle text-muted mr-1'"></i>
                          {{ o.name_kh }}
                        </label>
                      </div>
                    </div>

                    <!-- Section 3: Officers -->
                    <div class="bg-white p-2 rounded border mb-3">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="font-13 text-success"><i class="fas fa-user-check mr-1"></i> ៣. មន្ត្រីទទួលបន្ទុកជាក់លាក់៖</strong>
                        <span class="badge badge-success">{{ dgAnnotateForm.user_ids.length }} បានជ្រើស</span>
                      </div>
                      <div class="input-group input-group-sm mb-2">
                        <input
                          type="text"
                          class="form-control"
                          placeholder="ស្វែងរកឈ្មោះមន្ត្រី..."
                          v-model="officerDgSearch"
                        />
                      </div>
                      <div class="d-flex flex-wrap gap-2" style="max-height: 140px; overflow-y: auto;">
                        <label
                          v-for="u in filteredDgOfficerOptions"
                          :key="'dg_usr_' + u.id"
                          class="btn btn-xs mr-1 mb-1 font-khmer cursor-pointer border"
                          :class="dgAnnotateForm.user_ids.includes(u.id) ? 'btn-success text-white' : 'btn-light text-dark'"
                        >
                          <input
                            type="checkbox"
                            class="d-none"
                            :value="u.id"
                            v-model="dgAnnotateForm.user_ids"
                            @change="syncDgTargetsToAnnotation"
                          />
                          <i class="fas" :class="dgAnnotateForm.user_ids.includes(u.id) ? 'fa-check-circle mr-1' : 'fa-plus-circle text-muted mr-1'"></i>
                          {{ u.name_kh || u.name }} <small v-if="u.position?.title_kh">({{ u.position.title_kh }})</small>
                        </label>
                      </div>
                    </div>

                    <!-- Section 4: Working Groups -->
                    <div class="bg-white p-2 rounded border">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="font-13 text-indigo"><i class="fas fa-users-cog mr-1"></i> ៤. ក្រុមការងារ (Working Groups)៖</strong>
                        <span class="badge badge-indigo text-white">{{ dgAnnotateForm.working_group_ids.length }} បានជ្រើស</span>
                      </div>
                      <div class="d-flex flex-wrap gap-2" style="max-height: 130px; overflow-y: auto;">
                        <label
                          v-for="wg in recipientOptions.working_groups"
                          :key="'dg_wg_' + wg.id"
                          class="btn btn-xs mr-1 mb-1 font-khmer cursor-pointer border"
                          :class="dgAnnotateForm.working_group_ids.includes(wg.id) ? 'btn-indigo text-white' : 'btn-light text-dark'"
                        >
                          <input
                            type="checkbox"
                            class="d-none"
                            :value="wg.id"
                            v-model="dgAnnotateForm.working_group_ids"
                            @change="syncDgTargetsToAnnotation"
                          />
                          <i class="fas" :class="dgAnnotateForm.working_group_ids.includes(wg.id) ? 'fa-check-circle mr-1' : 'fa-plus-circle text-muted mr-1'"></i>
                          {{ wg.name_kh }}
                          <span class="badge badge-light border ml-1 text-xs" v-if="wg.leader_name_kh && wg.leader_name_kh !== 'មិនទាន់កំណត់'">
                            ប្រធាន៖ {{ wg.leader_name_kh }}
                          </span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Dispatch Notes -->
                <div class="mb-3" v-if="dgAnnotateForm.department_ids.length > 0 || dgAnnotateForm.office_ids.length > 0 || dgAnnotateForm.user_ids.length > 0 || dgAnnotateForm.working_group_ids.length > 0">
                  <label class="form-label font-weight-bold">សេចក្តីណែនាំបន្ថែមសម្រាប់អ្នកទទួល</label>
                  <textarea
                    class="form-control"
                    rows="2"
                    placeholder="កំណត់សម្គាល់បន្ថែម ឬសេចក្តីណែនាំ..."
                    v-model="dgAnnotateForm.dispatch_notes"
                  ></textarea>
                </div>

                <div class="modal-footer px-0 pb-0 pt-3 border-top">
                  <button type="button" class="btn btn-secondary" @click="showDgAnnotateModal = false">បោះបង់</button>
                  <button type="submit" class="btn btn-danger font-weight-bold" :disabled="submitting">
                    <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                    <i class="fas" :class="(dgAnnotateForm.department_ids.length > 0 || dgAnnotateForm.office_ids.length > 0 || dgAnnotateForm.user_ids.length > 0 || dgAnnotateForm.working_group_ids.length > 0) ? 'fa-paper-plane mr-1' : 'fa-save mr-1'"></i>
                    {{ (dgAnnotateForm.department_ids.length > 0 || dgAnnotateForm.office_ids.length > 0 || dgAnnotateForm.user_ids.length > 0 || dgAnnotateForm.working_group_ids.length > 0) ? 'រក្សាទុកចំណារ និងចាត់ចែងភ្លាមៗ' : 'រក្សាទុកចំណារ' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </Teleport>





    <!-- ==================== MODAL 6: ថ្នាក់ដឹកនាំពិនិត្យ និងចារឆ្លង (LEADERSHIP REVIEW & APPROVAL) ==================== -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showReviewResponseModal">
        <div class="modal-dialog modal-dialog-centered my-auto" :style="responseReviewStep === 'position' ? 'width: 100%; max-width: 980px;' : 'width: 100%; max-width: 650px;'">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden">
            <div class="modal-header bg-dark-custom text-white py-3 px-4 flex-shrink-0 align-items-center d-flex justify-content-between">
              <h5 class="modal-title font-khmer font-weight-bold mb-0">
                <i class="fas fa-clipboard-check text-warning mr-2"></i>
                <span v-if="responseReviewStep === 'form'">ពិនិត្យ និងចារឆ្លងលិខិតរដ្ឋបាល</span>
                <span v-else>រៀបចំទីតាំងមតិយោបល់ & ហត្ថលេខាលើរបាយការណ៍</span>
              </h5>
              <div class="d-flex align-items-center">
                <span v-if="responseReviewStep === 'position'" class="badge badge-light text-primary font-weight-bold mr-3 px-2 py-1">
                  ជំហានទី ២ / ២
                </span>
                <button type="button" class="close text-white" @click="showReviewResponseModal = false">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            </div>

            <!-- STEP 1: FORM -->
            <div class="modal-body p-4 font-khmer" v-if="responseReviewStep === 'form'">
              <!-- Document Summary -->
              <div class="alert alert-secondary py-2 font-13 mb-3">
                <div class="font-weight-bold text-dark">{{ selectedResponse?.title }}</div>
                <div class="small text-muted mt-1">
                  ឯកសារដើមលេខ៖ <strong>{{ selectedDoc?.general_inbound_number || '-' }}</strong> |
                  អ្នករៀបចំ៖ <strong>{{ selectedResponse?.drafted_by_user?.name_kh || selectedResponse?.drafted_by_user?.name || '-' }}</strong> |
                  ប្រភេទ៖ <strong>{{ selectedResponse?.response_type_kh || (selectedResponse?.response_type === 'SUBMISSION_NOTE' ? 'កំណត់បង្ហាញ' : (selectedResponse?.response_type === 'LETTER' ? 'លិខិត' : 'របាយការណ៍')) }}</strong>
                </div>
              </div>

              <!-- Action Selection -->
              <div class="form-group mb-3">
                <label class="font-weight-bold text-dark">សកម្មភាពសម្រេច <span class="text-danger">*</span></label>
                <div class="btn-group btn-group-toggle w-100">
                  <label class="btn btn-outline-primary btn-sm font-khmer cursor-pointer" :class="{ active: reviewActionForm.action === 'FORWARD' }" v-if="!isDg">
                    <input type="radio" value="FORWARD" v-model="reviewActionForm.action" />
                    <i class="fas fa-share mr-1"></i> បញ្ជូនបន្តឡើងលើ
                  </label>
                  <label class="btn btn-outline-danger btn-sm font-khmer cursor-pointer" :class="{ active: reviewActionForm.action === 'RETURN' }">
                    <input type="radio" value="RETURN" v-model="reviewActionForm.action" />
                    <i class="fas fa-undo mr-1"></i> បញ្ជូនត្រឡប់កែសម្រួល
                  </label>
                  <label class="btn btn-outline-success btn-sm font-khmer font-weight-bold cursor-pointer" :class="{ active: reviewActionForm.action === 'DG_APPROVE' }" v-if="isDg || isAdmin">
                    <input type="radio" value="DG_APPROVE" v-model="reviewActionForm.action" />
                    <i class="fas fa-signature mr-1"></i> ឯកភាព & ចុះហត្ថលេខា (អគ្គនាយក)
                  </label>
                </div>
              </div>

              <!-- Next Approver (if FORWARD) -->
              <div class="form-group mb-3" v-if="reviewActionForm.action === 'FORWARD'">
                <label class="font-weight-bold text-dark">ជ្រើសរើសថ្នាក់ដឹកនាំបន្ទាប់ <span class="text-danger">*</span></label>
                <select class="form-control font-weight-bold" v-model="reviewActionForm.forwarded_to_id" required>
                  <option value="" disabled>-- ជ្រើសរើសថ្នាក់ដឹកនាំ --</option>
                  <option v-for="c in availableResponseCandidates" :key="'rev_' + c.id" :value="c.id">
                    {{ c.name_kh || c.name }} - {{ c.position?.title_kh || '' }} ({{ c.office?.name_kh || c.department?.name_kh || '' }})
                  </option>
                </select>
              </div>

              <!-- Response Number (if DG_APPROVE) -->
              <div class="form-group mb-3" v-if="reviewActionForm.action === 'DG_APPROVE'">
                <label class="font-weight-bold text-dark">លេខលិខិតចេញផ្លូវការ (Response Number)</label>
                <input
                  type="text"
                  class="form-control"
                  placeholder="ឧ. ១២៣ ន.ប.ធ./..."
                  v-model="reviewActionForm.response_number"
                />
                <small class="text-muted">អាចកត់ត្រាលេខចេញផ្លូវការដែលបានចុះបញ្ជី ឬទុកឱ្យប្រព័ន្ធបង្កើត</small>
              </div>

              <!-- Comment -->
              <div class="form-group mb-0">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <label class="font-weight-bold text-dark mb-0">មតិយោបល់ / ចំណារពិនិត្យ</label>
                  <div class="d-flex flex-wrap gap-1">
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-primary mr-1 mb-1 font-weight-bold"
                      @click="reviewActionForm.comment = 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច'"
                    >
                      + គោរពជូន ឯ.ឧ អគ្គនាយក
                    </button>
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-info mr-1 mb-1 font-weight-bold"
                      @click="reviewActionForm.comment = 'សូមគោរពជូនលោកប្រធាននាយកដ្ឋាន\nមេត្តាពិនិត្យ និងសម្រេច'"
                    >
                      + គោរពជូន ប្រធាននាយកដ្ឋាន
                    </button>
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-success mr-1 mb-1 font-weight-bold"
                      @click="reviewActionForm.comment = 'បានពិនិត្យ និងឯកភាព'"
                    >
                      + បានពិនិត្យ និងឯកភាព
                    </button>
                  </div>
                </div>
                <textarea
                  class="form-control"
                  rows="3"
                  placeholder="កត់ត្រាមតិយោបល់ ឬចំណារណែនាំ..."
                  v-model="reviewActionForm.comment"
                ></textarea>
              </div>

              <!-- Leader's Review & Signature Placement onto Report -->
              <div class="card border rounded-lg p-3 bg-white mt-3 shadow-xs" v-if="reviewActionForm.action !== 'RETURN'">
                <div class="custom-control custom-checkbox mb-2">
                  <input
                    type="checkbox"
                    class="custom-control-input"
                    id="inboundEmbedReviewCheck"
                    v-model="reviewActionForm.embed_into_document"
                  />
                  <label class="custom-control-label font-weight-bold text-primary cursor-pointer" for="inboundEmbedReviewCheck">
                    <i class="fas fa-file-signature mr-1"></i> បញ្ចូលមតិយោបល់ និងហត្ថលេខាទៅក្នុងឯកសាររបាយការណ៍
                  </label>
                </div>

                <div v-if="reviewActionForm.embed_into_document" class="pl-3 border-left ml-2">
                  <div class="row">
                    <div class="col-md-6 mb-2">
                      <label class="small font-weight-bold text-dark mb-1">
                        <i class="fas fa-map-marker-alt text-danger mr-1"></i> ជ្រើសរើសទីតាំងចាប់ផ្តើមលើរបាយការណ៍៖
                      </label>
                      <select class="form-control form-control-sm font-weight-bold" v-model="reviewActionForm.placement">
                        <option value="SIDE_BY_SIDE_LEFT">ទន្ទឹមហត្ថលេខាអ្នករៀបចំ (ខាងឆ្វេង) - ស្តង់ដារ</option>
                        <option value="ABOVE_DRAFTER">ខាងលើហត្ថលេខាអ្នករៀបចំ</option>
                        <option value="BELOW_DRAFTER">ខាងក្រោមហត្ថលេខាអ្នករៀបចំ</option>
                        <option value="BOTTOM">ខាងក្រោមបង្អស់នៃរបាយការណ៍ (ចុងបញ្ចប់)</option>
                      </select>
                    </div>
                    <div class="col-md-6 mb-2">
                      <label class="small font-weight-bold text-dark mb-1">
                        <i class="fas fa-calendar-alt text-primary mr-1"></i> កាលបរិច្ឆេទចុះហត្ថលេខា៖
                      </label>
                      <input type="text" class="form-control form-control-sm" v-model="reviewActionForm.sign_date" placeholder="ឧ. ៤/១១/២៦" />
                    </div>
                  </div>

                  <!-- Signature Preview & Drawing -->
                  <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light border mt-1">
                    <div class="d-flex align-items-center">
                      <span class="small font-weight-bold text-muted mr-2">ហត្ថលេខា៖</span>
                      <img v-if="userStore.signature_image" :src="userStore.signature_image" alt="ហត្ថលេខា" style="height: 38px; max-width: 140px; object-fit: contain;" />
                      <span v-else class="small text-primary font-weight-bold" style="font-family: 'Brush Script MT', 'Segoe Script', cursive; font-size: 16pt;">
                        {{ userStore.name_kh || userStore.name }}
                      </span>
                    </div>
                    <button
                      type="button"
                      class="btn btn-xs btn-outline-primary font-weight-bold"
                      @click="showInboundReviewSignatureModal = true"
                    >
                      <i class="fas fa-pen mr-1"></i> {{ userStore.signature_image ? 'កែប្រែហត្ថលេខា' : '+ គូស/បង្ហោះហត្ថលេខា' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 2: POSITION ON REPORT (DRAGGABLE) -->
            <div class="modal-body p-3 bg-light" v-if="responseReviewStep === 'position'">
              <!-- Info Alert -->
              <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center justify-content-between shadow-xs">
                <div class="small">
                  <i class="fas fa-arrows-alt text-primary mr-2 fa-lg"></i>
                  <span><strong>ការណែនាំ៖</strong>
                    • <strong>រំកិល៖</strong> ចុចហើយអូសប្រអប់ទៅទីតាំងសមស្រប |
                    • <strong>ពង្រីក-បង្រួម៖</strong> ចុចអូសចំណុច Handle ជុំវិញប្រអប់ |
                    • <strong>កែប្រែមតិ/កាលបរិច្ឆេទ៖</strong> ចុចផ្ទាល់លើអក្សរក្នុងប្រអប់ដើម្បីកែសម្រួល
                  </span>
                </div>
                <button type="button" class="btn btn-xs btn-outline-secondary font-weight-bold" @click="resetResponseReviewPosition">
                  <i class="fas fa-undo mr-1"></i> កំណត់ទីតាំងដើម
                </button>
              </div>

              <!-- Document Sheet Scroll Area -->
              <div class="review-paper-scroll" style="max-height: 62vh; overflow-y: auto; background-color: #525659; padding: 20px; border-radius: 6px; display: flex; justify-content: center;">
                <div
                  ref="responseReviewPositionPaperRef"
                  class="review-position-paper bg-white shadow-lg"
                  style="width: 210mm; min-height: 297mm; padding: 25mm 20mm 20mm 25mm; box-sizing: border-box; position: relative;"
                  v-html="responseReviewWorkingHtml"
                ></div>
              </div>
            </div>

            <div class="modal-footer bg-white py-2 px-4 justify-content-between">
              <div v-if="responseReviewStep === 'position'">
                <button type="button" class="btn btn-outline-secondary px-3" @click="responseReviewStep = 'form'">
                  <i class="fas fa-arrow-left mr-1"></i> ត្រឡប់ក្រោយកែព័ត៌មាន
                </button>
              </div>
              <div v-else>
                <button type="button" class="btn btn-secondary px-3" @click="showReviewResponseModal = false">
                  បោះបង់
                </button>
              </div>

              <div>
                <!-- In Step 1 -->
                <template v-if="responseReviewStep === 'form'">
                  <button
                    v-if="reviewActionForm.embed_into_document && reviewActionForm.action !== 'RETURN' && selectedResponse?.content"
                    type="button"
                    class="btn btn-primary px-4 font-weight-bold"
                    @click="goToResponseReviewPositionStep"
                  >
                    <span>រៀបចំទីតាំងលើរបាយការណ៍ <i class="fas fa-arrow-right ml-1"></i></span>
                  </button>
                  <button
                    v-else
                    type="button"
                    class="btn px-4 font-weight-bold"
                    :class="reviewActionForm.action === 'RETURN' ? 'btn-danger' : 'btn-success'"
                    @click="submitReviewAction"
                    :disabled="submittingReview"
                  >
                    <i class="fas fa-check mr-1"></i> {{ reviewActionForm.action === 'RETURN' ? 'បញ្ជូនត្រឡប់កែសម្រួល' : 'អនុវត្តសកម្មភាព' }}
                  </button>
                </template>

                <!-- In Step 2 -->
                <template v-else>
                  <button
                    type="button"
                    class="btn btn-warning px-3 font-weight-bold mr-2 text-dark shadow-xs"
                    @click="saveAndHoldResponseReview"
                    :disabled="submittingReview"
                    title="រក្សាទុកមតិយោបល់ និងទីតាំងក្នុងដៃសិន ដោយមិនទាន់បញ្ជូនទៅថ្នាក់លើ"
                  >
                    <i class="fas fa-save mr-1"></i> រក្សាទុកក្នុងដៃសិន (ចាំចារឆ្លងតាមក្រោយ)
                  </button>
                  <button
                    type="button"
                    class="btn btn-success px-4 font-weight-bold"
                    @click="submitReviewAction"
                    :disabled="submittingReview"
                  >
                    <i class="fas fa-paper-plane mr-1"></i> {{ reviewActionForm.action === 'DG_APPROVE' ? 'ឯកភាព & ចុះហត្ថលេខាផ្លូវការ' : 'យល់ព្រម & ចារឆ្លងបន្ត' }}
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Inbound Review Signature Modal -->
    <SignatureModal v-model:show="showInboundReviewSignatureModal" />

    <!-- ==================== MODAL 7: មើលព័ត៌មានលម្អិត & ប្រវត្តិលំហូរ (DETAIL & AUDIT) ==================== -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showDetailModal">
        <div class="modal-dialog modal-xl modal-dialog-centered my-auto" style="width: 100%; max-width: 1140px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden d-flex flex-column" style="max-height: 92vh;">
            <div class="modal-header bg-dark-custom text-white py-3 px-4 flex-shrink-0 align-items-center">
              <h5 class="modal-title font-khmer font-weight-bold mb-0">
                <i class="fas fa-info-circle text-info mr-2"></i>ព័ត៌មានលម្អិត និងប្រវត្តិលំហូរឯកសារ
              </h5>
              <button type="button" class="close text-white" @click="showDetailModal = false">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body p-4 font-khmer flex-grow-1" v-if="selectedDoc" style="overflow-y: auto; max-height: calc(90vh - 120px);">
            <div class="row">
              <!-- Left Column: Document Overview -->
              <div class="col-lg-7 mb-3">
                <div class="card border rounded-lg h-100">
                  <div class="card-header bg-light py-2 font-weight-bold text-dark">
                    <i class="fas fa-file-alt text-primary mr-1"></i> ព័ត៌មានឯកសារចូល
                  </div>
                  <div class="card-body p-3 font-13">
                    <table class="table table-sm table-borderless mb-0">
                      <tbody>
                        <tr>
                          <td class="text-muted" style="width: 170px;">លេខឯកសារ៖</td>
                          <td><span class="badge badge-dark font-13">{{ selectedDoc.general_inbound_number || '---' }}</span></td>
                        </tr>
                        <tr>
                          <td class="text-muted">កាលបរិច្ឆេទ៖</td>
                          <td><strong>{{ formatDateKh(selectedDoc.received_date) }}</strong> ម៉ោង {{ selectedDoc.received_time }}</td>
                        </tr>
                        <tr>
                          <td class="text-muted">អ្នកប្រគល់៖</td>
                          <td><strong>{{ selectedDoc.deliverer_name }}</strong> {{ selectedDoc.deliverer_phone ? `(${selectedDoc.deliverer_phone})` : '' }}</td>
                        </tr>
                        <tr>
                          <td class="text-muted">ប្រភព</td>
                          <td><strong class="text-dark font-14">{{ selectedDoc.sender_organization }}</strong></td>
                        </tr>
                        <tr>
                          <td class="text-muted">លេខលិខិតដើម៖</td>
                          <td>{{ selectedDoc.external_reference_number || '---' }}</td>
                        </tr>
                        <tr>
                          <td class="text-muted">កម្មវត្ថុ៖</td>
                          <td><span class="font-weight-bold text-primary font-14">{{ selectedDoc.title }}</span></td>
                        </tr>
                        <tr>
                          <td class="text-muted">កម្រិតបន្ទាន់៖</td>
                          <td>
                            <span class="badge badge-warning text-dark">{{ selectedDoc.urgency_kh }}</span>
                          </td>
                        </tr>
                        <tr v-if="selectedDoc.deadline">
                          <td class="text-muted">កាលកំណត់ (Deadline)៖</td>
                          <td>
                            <span
                              class="badge font-13"
                              :class="{
                                'badge-danger': selectedDoc.deadline_status === 'OVERDUE' || selectedDoc.deadline_status === 'TODAY',
                                'badge-warning text-dark': selectedDoc.deadline_status === 'DUE_SOON',
                                'badge-info': selectedDoc.deadline_status === 'ON_TRACK',
                                'badge-light border text-muted': selectedDoc.deadline_status === 'RESOLVED'
                              }"
                            >
                              <i class="fas mr-1" :class="selectedDoc.deadline_status === 'OVERDUE' ? 'fa-exclamation-triangle' : (selectedDoc.deadline_status === 'RESOLVED' ? 'fa-check' : 'fa-clock')"></i>
                              {{ formatDateKh(selectedDoc.deadline) }} ({{ selectedDoc.deadline_remaining_kh }})
                            </span>
                          </td>
                        </tr>
                        <tr>
                          <td class="text-muted">ស្ថានភាពបច្ចុប្បន្ន៖</td>
                          <td>
                            <span class="badge px-2 py-1 font-12 shadow-2xs" :class="getSimplifiedStatus(selectedDoc).badgeClass">
                              <i :class="getSimplifiedStatus(selectedDoc).icon" class="mr-1"></i>
                              {{ getSimplifiedStatus(selectedDoc).label }} ({{ getSimplifiedStatus(selectedDoc).khLabel }})
                            </span>
                            <span class="small text-muted ml-2 font-weight-bold" v-if="getSimplifiedStatus(selectedDoc).subText">
                              • {{ getSimplifiedStatus(selectedDoc).subText }}
                            </span>
                          </td>
                        </tr>
                        <!-- DG Annotation Box -->
                        <tr v-if="selectedDoc.dg_annotation">
                          <td colspan="2" class="pt-3">
                            <div class="card border-danger bg-danger-light p-3 rounded">
                              <span class="font-weight-bold text-danger d-block mb-1">
                                <i class="fas fa-pen-nib mr-1"></i> ចំណារឯកឧត្តមអគ្គនាយក៖
                              </span>
                              <div class="font-weight-bold text-dark font-14">
                                {{ selectedDoc.dg_annotation }}
                              </div>
                              <div class="small text-muted mt-2 d-flex justify-content-between">
                                <span>តម្រូវការឆ្លើយតប៖ <strong>{{ selectedDoc.is_response_required ? 'តម្រូវឱ្យឆ្លើយតប (Path B)' : 'សម្រាប់ជ្រាប (Path A)' }}</strong></span>
                                <span>{{ selectedDoc.dg_annotated_at ? formatDateTimeKh(selectedDoc.dg_annotated_at) : '' }}</span>
                              </div>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>

                    <!-- Attached Documents -->
                    <div class="mt-3 pt-3 border-top">
                      <span class="font-weight-bold text-dark d-block mb-2">ឯកសារភ្ជាប់ / Scan៖</span>
                      <div class="d-flex flex-wrap gap-2">
                        <!-- Original Scan -->
                        <div class="btn-group mr-2 mb-2" v-if="selectedDoc.original_file_path">
                          <button
                            type="button"
                            class="btn btn-sm btn-outline-primary font-khmer"
                            @click="previewOriginalDoc(selectedDoc)"
                            title="មើលឯកសារ Scan ដើម"
                          >
                            <i class="fas fa-eye text-primary mr-1"></i> មើលឯកសារដើម (PDF)
                          </button>
                          <a
                            :href="getOriginalFileUrl(selectedDoc.id)"
                            download
                            class="btn btn-sm btn-outline-secondary"
                            title="ទាញយកឯកសារដើម"
                          >
                            <i class="fas fa-download"></i>
                          </a>
                        </div>

                        <!-- Annotated Scan -->
                        <div class="btn-group mb-2" v-if="selectedDoc.annotated_file_path">
                          <button
                            type="button"
                            class="btn btn-sm btn-outline-danger font-khmer"
                            @click="previewAnnotatedDoc(selectedDoc)"
                            title="មើលឯកសារមានចំណារ"
                          >
                            <i class="fas fa-signature text-danger mr-1"></i> មើលចំណារអគ្គនាយក (PDF)
                          </button>
                          <a
                            :href="getAnnotatedFileUrl(selectedDoc.id)"
                            download
                            class="btn btn-sm btn-outline-secondary"
                            title="ទាញយកចំណារអគ្គនាយក"
                          >
                            <i class="fas fa-download"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column: Flow Movements & Response History -->
              <div class="col-lg-5 mb-3">
                <div class="card border rounded-lg h-100 shadow-sm d-flex flex-column">
                  <!-- Tab Header: Inbound Movements & Response Flow -->
                  <div class="card-header bg-light py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-shrink-0">
                    <div class="d-flex align-items-center flex-wrap gap-1">
                      <button
                        type="button"
                        class="btn btn-sm mr-1 font-khmer font-weight-bold"
                        :class="detailFlowTab === 'inbound' ? 'btn-primary shadow-xs' : 'btn-outline-secondary'"
                        @click="detailFlowTab = 'inbound'"
                      >
                        <i class="fas fa-history mr-1"></i> លំហូរឯកសារចូល
                        <span class="badge badge-light ml-1" v-if="selectedDoc.movements?.length">{{ selectedDoc.movements.length }}</span>
                      </button>

                      <button
                        v-if="selectedDoc.responses && selectedDoc.responses.length > 0"
                        type="button"
                        class="btn btn-sm font-khmer font-weight-bold"
                        :class="detailFlowTab === 'responses' ? 'btn-info text-white shadow-xs' : 'btn-outline-info'"
                        @click="detailFlowTab = 'responses'"
                      >
                        <i class="fas fa-file-signature mr-1"></i> លំហូរឆ្លងរបាយការណ៍
                        <span class="badge badge-light ml-1">{{ selectedDoc.responses.length }}</span>
                      </button>
                    </div>

                    <span class="badge badge-warning text-dark font-11" v-if="selectedDoc.responses && selectedDoc.responses.length > 0 && detailFlowTab === 'inbound'">
                      <i class="fas fa-reply mr-1"></i> មានរបាយការណ៍
                    </span>
                  </div>

                  <div class="card-body p-3 overflow-auto flex-grow-1 font-13" style="max-height: 540px;">
                    <!-- TAB 1: Inbound Movements Flow with User Holding Duration -->
                    <div v-if="detailFlowTab === 'inbound'">
                      <div class="timeline" v-if="selectedDoc.movements && selectedDoc.movements.length > 0">
                        <div
                          v-for="(m, mIdx) in selectedDoc.movements"
                          :key="m.id"
                          class="mb-3 pl-3 border-left border-primary position-relative timeline-step"
                        >
                          <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap">
                            <span class="font-weight-bold text-dark font-13">
                              <i class="fas fa-check-circle text-primary mr-1"></i>
                              {{ m.action_kh }}
                            </span>
                            <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ formatDateTimeKh(m.created_at) }}</small>
                          </div>

                          <div class="small text-muted mb-1">
                            <i class="far fa-user text-secondary mr-1"></i>
                            <strong>{{ m.user?.name_kh || m.user?.name || 'ប្រព័ន្ធ' }}</strong>
                            <span class="text-secondary ml-1" v-if="m.user?.position?.title_kh">({{ m.user.position.title_kh }})</span>
                          </div>

                          <!-- Holding duration badge: រយៈពេលដែលស្ថិតនៅក្នុងដៃ User -->
                          <div class="my-1">
                            <span
                              class="badge font-11 py-1 px-2 shadow-2xs"
                              :class="mIdx === selectedDoc.movements.length - 1 && selectedDoc.status !== 'COMPLETED' ? 'badge-warning text-dark' : 'badge-light border text-dark'"
                            >
                              <i class="fas fa-stopwatch mr-1" :class="mIdx === selectedDoc.movements.length - 1 && selectedDoc.status !== 'COMPLETED' ? 'text-danger' : 'text-primary'"></i>
                              <strong>រយៈពេលកាន់កាប់៖</strong> {{ getMovementDuration(m, mIdx, selectedDoc.movements, selectedDoc) }}
                            </span>
                          </div>

                          <div class="text-secondary font-12 mt-1 bg-light p-2 rounded border" v-if="m.comment">
                            <em>«{{ m.comment }}»</em>
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-muted text-center py-4">
                        <i class="fas fa-inbox fa-2x text-black-50 mb-2 d-block"></i>
                        មិនទាន់មានប្រវត្តិផ្លាស់ប្តូរឡើយ
                      </div>

                      <!-- Bottom Shortcut if Response Exists -->
                      <div class="mt-4 pt-3 border-top" v-if="selectedDoc.responses && selectedDoc.responses.length > 0">
                        <div class="alert alert-info py-2 px-3 d-flex justify-content-between align-items-center mb-0">
                          <div>
                            <i class="fas fa-reply-all mr-1 text-primary"></i>
                            <strong>ឯកសារនេះមានរបាយការណ៍ឆ្លើយតប ({{ selectedDoc.responses.length }})</strong>
                          </div>
                          <button
                            type="button"
                            class="btn btn-xs btn-primary font-khmer shadow-sm"
                            @click="detailFlowTab = 'responses'"
                          >
                            <i class="fas fa-stream mr-1"></i> មើលលំហូរឆ្លង &rarr;
                          </button>
                        </div>
                      </div>
                    </div>

                    <!-- TAB 2: Hierarchical Response Workflow Flow (លំហូរឆ្លងរបាយការណ៍) -->
                    <div v-else-if="detailFlowTab === 'responses'">
                      <div v-if="!selectedDoc.responses || selectedDoc.responses.length === 0" class="text-center py-5 text-muted">
                        <i class="fas fa-file-excel fa-2x text-black-50 mb-2 d-block"></i>
                        មិនទាន់មានសេចក្តីព្រាងរបាយការណ៍ ឬលិខិតឆ្លើយតបនៅឡើយ
                      </div>

                      <div v-else v-for="resp in selectedDoc.responses" :key="resp.id" class="card border rounded-lg mb-3 shadow-xs">
                        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                          <div>
                            <span class="badge badge-primary mr-2">{{ resp.response_type_kh || 'របាយការណ៍' }}</span>
                            <strong class="text-dark font-14">{{ resp.title }}</strong>
                            <span class="badge badge-dark ml-2" v-if="resp.response_number">លេខ៖ {{ resp.response_number }}</span>
                          </div>
                          <div>
                            <span
                              class="badge"
                              :class="resp.status === 'APPROVED_BY_DG' ? 'badge-success' : (resp.status === 'RETURNED' ? 'badge-danger' : 'badge-warning text-dark')"
                            >
                              {{ resp.status_kh }} ({{ resp.stage_kh }})
                            </span>
                          </div>
                        </div>

                        <div class="card-body p-3 font-13">
                          <div class="text-muted small mb-2 pb-2 border-bottom d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                              <i class="fas fa-user-edit mr-1 text-primary"></i> រៀបចំដោយ៖
                              <strong class="text-dark">{{ resp.drafted_by_user?.name_kh || resp.drafted_by_user?.name }}</strong>
                              <span class="text-secondary ml-1" v-if="resp.drafted_by_user?.position?.title_kh">({{ resp.drafted_by_user.position.title_kh }})</span>
                              <span class="mx-2">•</span>
                              <i class="far fa-calendar-alt mr-1"></i> {{ formatDateTimeKh(resp.created_at) }}
                            </div>

                            <div class="btn-group btn-group-sm mt-1 mt-sm-0">
                              <button
                                v-if="resp.content"
                                type="button"
                                class="btn btn-xs btn-outline-info font-khmer"
                                @click="previewResponseHtml(resp)"
                                title="មើលខ្លឹមសារពេញ"
                              >
                                <i class="fas fa-eye mr-1"></i> មើលខ្លឹមសារ
                              </button>
                              <button
                                v-if="resp.file_path"
                                type="button"
                                class="btn btn-xs btn-outline-danger font-khmer"
                                @click="previewResponseDoc(resp)"
                                title="មើលឯកសារ PDF"
                              >
                                <i class="fas fa-file-pdf mr-1"></i> PDF
                              </button>
                              <a
                                v-if="resp.file_path"
                                :href="getResponseFileUrl(resp.id)"
                                download
                                class="btn btn-xs btn-outline-secondary font-khmer"
                                title="ទាញយកឯកសារ"
                              >
                                <i class="fas fa-download mr-1"></i> ទាញយក
                              </a>
                              <button
                                v-if="canDeleteResponse(resp)"
                                type="button"
                                class="btn btn-xs btn-outline-danger font-khmer ml-1"
                                @click="deleteResponseAction(resp)"
                                :title="resp.status === 'RETURNED' ? 'លុបឯកសារដែលថ្នាក់ដឹកនាំបញ្ជូនត្រឡប់' : 'លុបសេចក្តីព្រាង'"
                              >
                                <i class="fas fa-trash mr-1"></i> លុប
                              </button>
                            </div>
                          </div>

                          <!-- Active Custodian Banner if under review -->
                          <div
                            v-if="resp.status === 'UNDER_REVIEW' && resp.current_approver"
                            class="alert alert-warning py-2 px-3 mb-3 font-12 d-flex justify-content-between align-items-center"
                          >
                            <span>
                              <i class="fas fa-user-clock mr-1 text-warning"></i>
                              កំពុងស្ថិតនៅដៃ៖ <strong>{{ resp.current_approver.name_kh || resp.current_approver.name }}</strong>
                              <span class="text-secondary ml-1" v-if="resp.current_approver.position?.title_kh">({{ resp.current_approver.position.title_kh }})</span>
                            </span>
                            <span class="badge badge-warning text-dark font-11">
                              <i class="fas fa-stopwatch mr-1"></i>
                              {{ getApprovalDuration(resp.approvals?.[resp.approvals?.length - 1], (resp.approvals?.length || 1) - 1, resp.approvals || [], resp) }}
                            </span>
                          </div>

                          <!-- Approval Ladder / Workflow timeline (លំហូរឆ្លង) -->
                          <div>
                            <strong class="d-block mb-2 text-dark font-12">
                              <i class="fas fa-layer-group text-primary mr-1"></i>
                              ប្រវត្តិឆ្លង និងអនុម័តតាមឋានានុក្រម (Review & Approval Flow)៖
                            </strong>

                            <div v-if="!resp.approvals || resp.approvals.length === 0" class="text-muted small py-2 pl-3">
                              <em>មិនទាន់មានកំណត់ត្រាពិនិត្យឆ្លងនៅឡើយ</em>
                            </div>

                            <div class="timeline-approvals" v-else>
                              <div
                                v-for="(app, aIdx) in resp.approvals"
                                :key="app.id"
                                class="mb-2 pl-3 border-left position-relative timeline-step"
                                :class="app.action === 'DG_APPROVE' ? 'border-success' : (app.action === 'RETURN' ? 'border-danger' : 'border-info')"
                              >
                                <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap">
                                  <div>
                                    <span class="badge badge-info mr-1 font-11">{{ app.stage_kh }}</span>
                                    <span
                                      class="badge font-11 mr-1"
                                      :class="app.action === 'DG_APPROVE' ? 'badge-success' : (app.action === 'RETURN' ? 'badge-danger' : (app.action === 'SUBMIT' ? 'badge-primary' : 'badge-dark'))"
                                    >
                                      {{ app.action_kh }}
                                    </span>
                                    <strong class="text-dark font-12">
                                      {{ app.user?.name_kh || app.user?.name || 'ថ្នាក់ដឹកនាំ' }}
                                    </strong>
                                    <span class="text-secondary small ml-1" v-if="app.user?.position?.title_kh">
                                      ({{ app.user.position.title_kh }})
                                    </span>
                                    <span v-if="app.forwarded_to" class="text-primary font-12 font-weight-bold ml-1">
                                      &rarr; {{ app.forwarded_to.name_kh || app.forwarded_to.name }}
                                      <small class="text-secondary" v-if="app.forwarded_to.position?.title_kh">({{ app.forwarded_to.position.title_kh }})</small>
                                    </span>
                                  </div>
                                  <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ formatDateTimeKh(app.created_at) }}</small>
                                </div>

                                <!-- Holding / Review Duration Badge -->
                                <div class="my-1">
                                  <span class="badge badge-light border text-dark font-11 py-1 px-2 shadow-2xs">
                                    <i class="fas fa-stopwatch text-warning mr-1"></i>
                                    <strong>រយៈពេលពិនិត្យ៖</strong> {{ getApprovalDuration(app, aIdx, resp.approvals, resp) }}
                                  </span>
                                </div>

                                <!-- Comment / Directive -->
                                <div class="mt-1 text-secondary font-12 bg-light p-2 rounded border" v-if="app.comment">
                                  <em>«{{ app.comment }}»</em>
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
            </div>
          </div>
            <div class="modal-footer font-khmer justify-content-between bg-light py-2 px-4 border-top flex-shrink-0">
              <!-- Left side: Main operational actions (ទទួលជ្រាប, ចាត់ចែងបន្ត, បង្កើតរបាយការណ៍) -->
              <div class="d-flex align-items-center flex-wrap">
                <!-- Step 5A: Acknowledge & Complete -->
                <button
                  v-if="selectedDoc && canAcknowledge(selectedDoc)"
                  class="btn btn-success font-weight-bold text-white shadow-sm mr-2 mb-1"
                  title="ទទួលជ្រាប និងបញ្ចប់ដំណើរការឯកសារ"
                  @click="acknowledgeAction(selectedDoc)"
                >
                  <i class="fas fa-check-double mr-1"></i> ទទួលជ្រាប និងបញ្ចប់
                </button>

                <!-- Step 5C: Cascading Forward / Re-assign Downward -->
                <button
                  v-if="selectedDoc && canForward(selectedDoc)"
                  class="btn btn-info font-weight-bold text-white shadow-sm mr-2 mb-1"
                  title="ចាត់ចែង និងបញ្ជូនបន្តការងារទៅកាន់មន្ត្រីក្រោមឱវាទ"
                  @click="showDetailModal = false; openForwardModal(selectedDoc)"
                >
                  <i class="fas fa-directions mr-1"></i> ចាត់ចែង និងបញ្ជូនបន្ត
                </button>

                <!-- Action: Create Outbound Report / Response -->
                <router-link
                  v-if="selectedDoc && canCreateReport(selectedDoc)"
                  :to="{ name: 'outbound-documents', query: { link_inbound_id: selectedDoc.id } }"
                  class="btn btn-primary font-weight-bold shadow-sm mr-2 mb-1"
                  @click="showDetailModal = false"
                  title="បង្កើតរបាយការណ៍ ឬកំណត់បង្ហាញឆ្លើយតប"
                >
                  <i class="fas fa-file-export mr-1"></i> បង្កើតរបាយការណ៍ / លិខិតឆ្លើយតប
                </router-link>
              </div>

              <!-- Right side: Administrative and Dismiss actions -->
              <div class="d-flex align-items-center">
                <button
                  v-if="selectedDoc && canEditDocument(selectedDoc)"
                  class="btn btn-outline-warning text-dark font-weight-bold mr-2 mb-1"
                  title="កែប្រែព័ត៌មានឯកសារនេះ (ករណីបញ្ចូលខុស)"
                  @click="openEditModal(selectedDoc)"
                >
                  <i class="fas fa-edit mr-1"></i> កែប្រែព័ត៌មាន
                </button>
                <button
                  v-if="selectedDoc && canDeleteDocument(selectedDoc)"
                  class="btn btn-outline-danger font-weight-bold mr-2 mb-1"
                  title="លុបឯកសារចូលនេះចេញ (ករណីបញ្ចូលខុស)"
                  @click="deleteDocumentAction(selectedDoc)"
                >
                  <i class="fas fa-trash-alt mr-1"></i> លុបឯកសារ
                </button>
                <button type="button" class="btn btn-secondary px-3 mb-1" @click="showDetailModal = false">បិទ</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- 8. Modal ចាត់ចែង និងបញ្ជូនបន្តតាមឋានានុក្រម (Cascading Forward Modal) -->
    <Teleport to="body">
      <div
        class="modal fade show d-block font-khmer"
        tabindex="-1"
        v-if="showForwardModal && selectedDoc"
        style="background: rgba(0, 0, 0, 0.6); z-index: 1060;"
      >
        <div class="modal-dialog modal-lg modal-dialog-centered my-auto" style="width: 100%; max-width: 800px;">
          <div class="modal-content border-0 shadow-2xl rounded-lg overflow-hidden">
            <div class="modal-header bg-primary text-white py-3">
              <h5 class="modal-title font-weight-bold">
                <i class="fas fa-directions mr-2"></i>ចាត់ចែង និងបញ្ជូនបន្តតាមឋានានុក្រម
              </h5>
              <button type="button" class="close text-white" @click="showForwardModal = false">
                <span>&times;</span>
              </button>
            </div>

            <form @submit.prevent="handleForwardSubmit">
              <div class="modal-body p-4">
                <!-- Summary Card -->
                <div class="card bg-light border-0 rounded-lg p-3 mb-3">
                  <div class="row">
                    <div class="col-sm-6 mb-2">
                      <span class="text-muted small d-block">លេខឯកសារ</span>
                      <span class="font-weight-bold text-dark font-15">{{ selectedDoc.general_inbound_number }}</span>
                    </div>
                    <div class="col-12 mb-2">
                      <span class="text-muted small d-block">កម្មវត្ថុ</span>
                      <span class="font-weight-bold text-dark">{{ selectedDoc.title }}</span>
                    </div>
                    <div class="col-12" v-if="selectedDoc.dg_annotation">
                      <span class="text-muted small d-block font-weight-bold text-danger">ខ្លឹមសារចំណារឯកឧត្តមអគ្គនាយក៖</span>
                      <div class="p-2 bg-white rounded border border-danger-subtle font-13 text-dark">
                        {{ selectedDoc.dg_annotation }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Target Type Selector -->
                <div class="form-group mb-3">
                  <label class="font-weight-bold text-dark">
                    ជ្រើសរើសប្រភេទគោលដៅចាត់ចែងបន្ត <span class="text-danger">*</span>
                  </label>
                  <div class="d-flex flex-wrap gap-3">
                    <div
                      v-for="opt in forwardTargetTypeOptions"
                      :key="opt.value"
                      class="custom-control custom-radio mr-3 mb-2"
                    >
                      <input
                        type="radio"
                        :id="'fwdTarget_' + opt.value"
                        name="fwd_target_type"
                        class="custom-control-input"
                        :value="opt.value"
                        v-model="forwardForm.target_type"
                      />
                      <label class="custom-control-label cursor-pointer" :for="'fwdTarget_' + opt.value">
                        <i :class="opt.icon" class="mr-1"></i>{{ opt.label }}
                      </label>
                    </div>
                  </div>
                </div>

                <!-- Department Dropdown (Admin / Leadership only) -->
                <div class="form-group mb-3" v-if="forwardForm.target_type === 'DEPARTMENT'">
                  <label class="font-weight-bold text-dark">នាយកដ្ឋានគោលដៅ <span class="text-danger">*</span></label>
                  <select class="form-control" v-model="forwardForm.target_department_id" required>
                    <option value="" disabled>-- សូមជ្រើសរើសនាយកដ្ឋាន --</option>
                    <option v-for="d in recipientOptions.departments" :key="d.id" :value="d.id">
                      {{ d.name_kh }}
                    </option>
                  </select>
                </div>

                <!-- Office Dropdown -->
                <div class="form-group mb-3" v-if="forwardForm.target_type === 'OFFICE'">
                  <label class="font-weight-bold text-dark">ការិយាល័យគោលដៅ <span class="text-danger">*</span></label>
                  <select class="form-control" v-model="forwardForm.target_office_id" required>
                    <option value="" disabled>-- សូមជ្រើសរើសការិយាល័យ --</option>
                    <option v-for="o in availableForwardOffices" :key="o.id" :value="o.id">
                      {{ o.name_kh }} <span v-if="o.department?.name_kh">({{ o.department.name_kh }})</span>
                    </option>
                  </select>
                  <div v-if="availableForwardOffices.length === 0" class="text-danger font-13 mt-1">
                    ពុំមានការិយាល័យចំណុះដែលអាចចាត់បន្តបានឡើយ
                  </div>
                </div>

                <!-- Officer Selection (Single or Multiple) -->
                <div class="form-group mb-3" v-if="forwardForm.target_type === 'OFFICER' || forwardForm.target_type === 'WG_MEMBER'">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="font-weight-bold text-dark m-0">
                      {{ forwardForm.target_type === 'WG_MEMBER' ? 'សមាជិកក្រុមការងារទទួលបន្ទុក' : 'មន្ត្រីក្រោមឱវាទទទួលបន្ទុកចាត់ចែង' }} <span class="text-danger">*</span>
                    </label>
                    <span class="badge badge-primary">{{ forwardForm.user_ids.length }} នាក់បានជ្រើស</span>
                  </div>
                  <div class="input-group input-group-sm mb-2">
                    <input
                      type="text"
                      class="form-control"
                      placeholder="ស្វែងរកឈ្មោះមន្ត្រី..."
                      v-model="officerForwardSearch"
                    />
                  </div>
                  <div class="d-flex flex-wrap gap-2 border rounded p-2 bg-white" style="max-height: 180px; overflow-y: auto;">
                    <label
                      v-for="u in filteredForwardOfficers"
                      :key="u.id"
                      class="btn btn-xs mr-1 mb-1 font-khmer cursor-pointer border"
                      :class="forwardForm.user_ids.includes(u.id) ? 'btn-success text-white' : 'btn-light text-dark'"
                    >
                      <input
                        type="checkbox"
                        class="d-none"
                        :value="u.id"
                        v-model="forwardForm.user_ids"
                      />
                      <i class="fas" :class="forwardForm.user_ids.includes(u.id) ? 'fa-check-circle mr-1' : 'fa-plus-circle text-muted mr-1'"></i>
                      {{ u.name_kh || u.name }}
                      <small v-if="u.position?.title_kh" class="ml-1">({{ u.position.title_kh }})</small>
                      <small v-if="u.office?.name_kh" class="text-muted ml-1">- {{ u.office.name_kh }}</small>
                    </label>
                    <div v-if="filteredForwardOfficers.length === 0" class="text-muted font-13 p-2">
                      ពុំមានមន្ត្រីក្រោមឱវាទដែលអាចចាត់ចែងបន្តបានឡើយ
                    </div>
                  </div>
                  <small class="text-muted">អាចជ្រើសរើសមន្ត្រីម្នាក់ ឬច្រើននាក់ដើម្បីចាត់ចែងបន្ត</small>
                </div>

                <!-- Working Group Selection (Admin / Leadership only) -->
                <div class="form-group mb-3" v-if="forwardForm.target_type === 'WORKING_GROUP'">
                  <label class="font-weight-bold text-dark">ក្រុមការងារគោលដៅ <span class="text-danger">*</span></label>
                  <select class="form-control" v-model="forwardForm.target_working_group_id" required>
                    <option value="" disabled>-- សូមជ្រើសរើសក្រុមការងារ --</option>
                    <option v-for="wg in recipientOptions.working_groups" :key="wg.id" :value="wg.id">
                      {{ wg.name_kh }} <span v-if="wg.leader_name_kh">(ប្រធាន៖ {{ wg.leader_name_kh }})</span>
                    </option>
                  </select>
                </div>

                <!-- Forwarding Notes / Instruction -->
                <div class="form-group mb-0">
                  <label class="font-weight-bold text-dark">ចំណារណែនាំបន្ត (Forwarding Notes / Instruction)</label>
                  <textarea
                    class="form-control"
                    rows="3"
                    v-model="forwardForm.forwarding_notes"
                    placeholder="ឧ. ជូនលោកប្រធាននាយកដ្ឋានពិនិត្យ និងចាត់ចែងបន្ត / ជូនលោក X រៀបចំសេចក្តីព្រាងឆ្លើយតប..."
                  ></textarea>
                </div>
              </div>

              <div class="modal-footer font-khmer bg-light">
                <button type="button" class="btn btn-secondary" @click="showForwardModal = false">
                  បោះបង់
                </button>
                <button type="submit" class="btn btn-primary px-4" :disabled="forwarding">
                  <i class="fas fa-paper-plane mr-1" v-if="!forwarding"></i>
                  <i class="fas fa-spinner fa-spin mr-1" v-else></i>
                  បញ្ជូនបន្ត
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- 9. Modal កំណត់ និងតេស្តការជូនដំណឹងតាម Telegram Bot -->
    <Teleport to="body">
      <div
        class="modal fade show d-block font-khmer"
        tabindex="-1"
        v-if="showTelegramModal"
        style="background: rgba(0, 0, 0, 0.6); z-index: 1060;"
      >
        <div class="modal-dialog modal-md modal-dialog-centered my-auto" style="width: 100%; max-width: 600px;">
          <div class="modal-content border-0 shadow-2xl rounded-lg overflow-hidden">
            <div class="modal-header bg-info text-white py-3">
              <h5 class="modal-title font-weight-bold">
                <i class="fab fa-telegram-plane mr-2"></i>ការកំណត់ការជូនដំណឹងតាម Telegram Bot
              </h5>
              <button type="button" class="close text-white" @click="showTelegramModal = false">
                <span>&times;</span>
              </button>
            </div>

            <form @submit.prevent="saveTelegramSettings">
              <div class="modal-body p-4">
                <!-- Telegram Bot Info Banner -->
                <div class="alert alert-light border border-info p-3 rounded mb-3">
                  <div class="d-flex align-items-center mb-2">
                    <i class="fab fa-telegram fa-2x text-info mr-2"></i>
                    <div>
                      <strong class="text-dark d-block">TRMS Official Telegram Bot</strong>
                      <a href="https://t.me/trms_regulator_bot" target="_blank" class="text-primary font-weight-bold">
                        @trms_regulator_bot <i class="fas fa-external-link-alt font-11"></i>
                      </a>
                    </div>
                  </div>
                  <div class="small text-muted" style="line-height: 1.5;">
                    របៀបភ្ជាប់៖
                    <ol class="pl-3 mb-0 mt-1">
                      <li>ចុច link <b>@trms_regulator_bot</b> រួចចុច <b>Start</b> ក្នុង Telegram</li>
                      <li>ស្វែងរក Chat ID របស់អ្នក (តាមរយៈ bot <code>@userinfobot</code>)</li>
                      <li>ចម្លង Chat ID មកដាក់ក្នុងប្រអប់ខាងក្រោម រួចចុច <b>សាកល្បងផ្ញើសារ</b></li>
                    </ol>
                  </div>
                </div>

                <!-- Chat ID Input -->
                <div class="form-group mb-3">
                  <label class="font-weight-bold text-dark">
                    Telegram Chat ID <span class="text-danger">*</span>
                  </label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                    </div>
                    <input
                      type="text"
                      class="form-control font-monospace"
                      v-model="telegramForm.chat_id"
                      placeholder="ឧ. 123456789"
                      required
                    />
                  </div>
                  <small class="form-text text-muted">Telegram Chat ID គឺជាលេខសម្គាល់គណនី Telegram របស់អ្នក។</small>
                </div>

                <!-- Username Input -->
                <div class="form-group mb-3">
                  <label class="font-weight-bold text-dark">
                    Telegram Username (បើមាន)
                  </label>
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <span class="input-group-text">@</span>
                    </div>
                    <input
                      type="text"
                      class="form-control"
                      v-model="telegramForm.username"
                      placeholder="ឧ. username"
                    />
                  </div>
                </div>

                <!-- Test Button & Status Message -->
                <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded mb-2">
                  <span class="small text-muted">
                    <i class="fas fa-info-circle mr-1"></i>សាកល្បងមុនពេលរក្សាទុក
                  </span>
                  <button
                    type="button"
                    class="btn btn-sm btn-outline-info"
                    :disabled="testingTelegram || !telegramForm.chat_id"
                    @click="testTelegramConnection"
                  >
                    <i class="fas fa-spinner fa-spin mr-1" v-if="testingTelegram"></i>
                    <i class="fas fa-paper-plane mr-1" v-else></i>
                    សាកល្បងផ្ញើសារ (Test)
                  </button>
                </div>
              </div>

              <div class="modal-footer font-khmer bg-light">
                <button type="button" class="btn btn-secondary" @click="showTelegramModal = false">
                  បោះបង់
                </button>
                <button type="submit" class="btn btn-primary px-4" :disabled="savingTelegram">
                  <i class="fas fa-spinner fa-spin mr-1" v-if="savingTelegram"></i>
                  <i class="fas fa-save mr-1" v-else></i>
                  រក្សាទុក
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- 10. Modal មើលឯកសារ PDF (Inline PDF Viewer Modal) -->
    <Teleport to="body">
      <div
        class="modal fade show d-block font-khmer"
        tabindex="-1"
        v-if="showPdfModal"
        style="background: rgba(0, 0, 0, 0.75); z-index: 1075;"
      >
        <div class="modal-dialog modal-xl modal-dialog-centered" style="max-width: 94vw; height: 92vh; margin: 2vh auto;">
          <div class="modal-content h-100 shadow-2xl border-0 rounded-lg overflow-hidden d-flex flex-column">
            <div class="modal-header bg-dark text-white py-2 px-3 align-items-center flex-shrink-0">
              <h5 class="modal-title font-khmer font-weight-bold font-15 mb-0 text-truncate" style="max-width: 60vw;">
                <i class="fas fa-file-pdf text-danger mr-2"></i>{{ pdfModalTitle }}
              </h5>
              <div class="d-flex align-items-center">
                <a
                  :href="pdfModalDownloadUrl || pdfModalUrl"
                  target="_blank"
                  class="btn btn-xs btn-outline-light mr-2 font-khmer"
                  title="បើកក្នុងផ្ទាំងថ្មី (New Tab)"
                >
                  <i class="fas fa-external-link-alt mr-1"></i> បើកផ្ទាំងថ្មី
                </a>
                <a
                  :href="pdfModalDownloadUrl || pdfModalUrl"
                  download
                  class="btn btn-xs btn-warning text-dark font-weight-bold mr-2 font-khmer"
                  title="ទាញយកឯកសារ"
                >
                  <i class="fas fa-download mr-1"></i> ទាញយក
                </a>
                <button type="button" class="close text-white ml-2" @click="closePdfModal">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            </div>
            <div class="modal-body p-0 position-relative flex-grow-1" style="background: #525659;">
              <div
                v-if="isPdfLoading"
                class="position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white"
                style="background: rgba(0,0,0,0.5); z-index: 10; top: 0; left: 0;"
              >
                <div class="spinner-border text-light mb-2" role="status"></div>
                <span class="font-khmer font-14">កំពុងផ្ទុកឯកសារ PDF...</span>
              </div>
              <div
                v-else-if="pdfError"
                class="position-absolute w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white p-4"
                style="background: #343a40; z-index: 10; top: 0; left: 0;"
              >
                <i class="fas fa-exclamation-triangle text-warning fa-3x mb-3"></i>
                <h5 class="font-khmer font-weight-bold mb-2">មិនអាចបើកបង្ហាញឯកសារ PDF ក្នុងផ្ទាំងនេះបានទេ</h5>
                <p class="font-khmer text-muted font-13 mb-3 text-center">
                  ឯកសារអាចមានទំហំធំ ឬតម្រូវឱ្យទាញយកដោយផ្ទាល់។ សូមចុចប៊ូតុងខាងក្រោមដើម្បីទាញយក ឬបើកមើលក្នុង Tab ថ្មី។
                </p>
                <div>
                  <a :href="pdfModalDownloadUrl || pdfModalUrl" download class="btn btn-warning text-dark font-weight-bold font-khmer mr-2">
                    <i class="fas fa-download mr-1"></i> ទាញយក PDF
                  </a>
                  <a :href="pdfModalDownloadUrl || pdfModalUrl" target="_blank" class="btn btn-outline-light font-khmer">
                    <i class="fas fa-external-link-alt mr-1"></i> បើកមើលក្នុង Tab ថ្មី
                  </a>
                </div>
              </div>
              <iframe
                v-if="pdfModalUrl && !pdfError"
                :src="pdfModalUrl"
                class="w-100 h-100 border-0"
                @load="isPdfLoading = false"
                style="min-height: 100%; display: block;"
              ></iframe>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- 11. Modal គ្រប់គ្រងស្ថាប័ន / អង្គភាពផ្ញើឯកសារ (Manage Sender Organizations Modal) -->
    <Teleport to="body">
      <div
        class="modal fade show d-block font-khmer"
        tabindex="-1"
        v-if="showManageOrgModal"
        style="background: rgba(0, 0, 0, 0.65); z-index: 1070;"
      >
        <div class="modal-dialog modal-lg modal-dialog-centered my-auto" style="width: 100%; max-width: 850px;">
          <div class="modal-content border-0 shadow-2xl rounded-lg overflow-hidden">
            <div class="modal-header bg-dark-custom text-white py-3 align-items-center">
            <h5 class="modal-title font-weight-bold mb-0">
              <i class="fas fa-building text-warning mr-2"></i>គ្រប់គ្រងបញ្ជីស្ថាប័ន / អង្គភាពផ្ញើឯកសារ
            </h5>
            <button type="button" class="close text-white" @click="closeManageOrgModal">
              <span>&times;</span>
            </button>
          </div>

          <div class="modal-body p-3 font-khmer">
            <!-- Nav Tabs -->
            <ul class="nav nav-pills mb-3">
              <li class="nav-item">
                <a
                  class="nav-link cursor-pointer"
                  :class="{ active: manageOrgActiveTab === 'list' }"
                  @click="manageOrgActiveTab = 'list'"
                >
                  <i class="fas fa-list mr-1"></i> បញ្ជីស្ថាប័នដែលមានស្រាប់ ({{ senderOrganizations.length }})
                </a>
              </li>
              <li class="nav-item ml-2">
                <a
                  class="nav-link cursor-pointer"
                  :class="{ active: manageOrgActiveTab === 'create' }"
                  @click="manageOrgActiveTab = 'create'"
                >
                  <i class="fas fa-plus mr-1"></i> + បន្ថែមស្ថាប័នថ្មី
                </a>
              </li>
            </ul>

            <!-- Tab 1: List & Search & Delete -->
            <div v-if="manageOrgActiveTab === 'list'">
              <div class="input-group input-group-sm mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input
                  type="text"
                  class="form-control"
                  placeholder="ស្វែងរកក្នុងបញ្ជីស្ថាប័ន (ឈ្មោះខ្មែរ / English / Code)..."
                  v-model="manageOrgSearch"
                />
                <div class="input-group-append" v-if="manageOrgSearch">
                  <button class="btn btn-outline-secondary" type="button" @click="manageOrgSearch = ''">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>

              <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                <table class="table table-hover table-bordered table-sm font-13 align-middle mb-0">
                  <thead class="thead-light sticky-top">
                    <tr>
                      <th style="width: 50px;" class="text-center">#</th>
                      <th>ឈ្មោះស្ថាប័ន (ខ្មែរ)</th>
                      <th style="width: 100px;" class="text-center">កូដ</th>
                      <th style="width: 130px;" class="text-center">ចំណាត់ថ្នាក់</th>
                      <th style="width: 90px;" class="text-center">សកម្មភាព</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(org, idx) in filteredManageOrgs" :key="org.id">
                      <td class="text-center text-muted">{{ idx + 1 }}</td>
                      <td>
                        <span class="font-weight-bold text-dark">{{ org.name_kh }}</span>
                        <div v-if="org.name_en" class="font-11 text-muted">{{ org.name_en }}</div>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-light border font-monospace">{{ org.code || '---' }}</span>
                      </td>
                      <td class="text-center">
                        <span class="badge badge-secondary">{{ getCategoryLabelKh(org.category) }}</span>
                      </td>
                      <td class="text-center">
                        <button
                          type="button"
                          class="btn btn-xs btn-outline-danger"
                          @click="confirmDeleteOrg(org)"
                          title="លុបស្ថាប័ននេះចេញពីបញ្ជី"
                        >
                          <i class="fas fa-trash-alt mr-1"></i>លុប
                        </button>
                      </td>
                    </tr>
                    <tr v-if="filteredManageOrgs.length === 0">
                      <td colspan="5" class="text-center text-muted py-4">
                        មិនមានស្ថាប័នត្រូវគ្នានឹងការស្វែងរកឡើយ
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Tab 2: Create New -->
            <div v-else-if="manageOrgActiveTab === 'create'">
              <form @submit.prevent="submitCreateSenderOrg">
                <div class="form-group mb-3">
                  <label class="font-weight-bold text-dark">
                    ឈ្មោះស្ថាប័ន / អង្គភាព (ខ្មែរ) <span class="text-danger">*</span>
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    placeholder="ឧ. ក្រសួងកសិកម្ម រុក្ខាប្រមាញ់ និងនេសាទ"
                    v-model="newOrgForm.name_kh"
                    required
                  />
                </div>

                <div class="form-group mb-3">
                  <label class="font-weight-bold text-dark">
                    ឈ្មោះជាភាសាអង់គ្លេស (បើមាន)
                  </label>
                  <input
                    type="text"
                    class="form-control"
                    placeholder="ឧ. Ministry of Agriculture..."
                    v-model="newOrgForm.name_en"
                  />
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">
                      អក្សរកាត់ / កូដ (Code)
                    </label>
                    <input
                      type="text"
                      class="form-control font-monospace"
                      placeholder="ឧ. MAFF"
                      v-model="newOrgForm.code"
                    />
                  </div>

                  <div class="col-md-6 mb-3">
                    <label class="font-weight-bold text-dark">
                      ចំណាត់ថ្នាក់ស្ថាប័ន
                    </label>
                    <select class="form-control" v-model="newOrgForm.category">
                      <option value="MINISTRY">ក្រសួង / ស្ថាប័នជាតិ</option>
                      <option value="REGULATOR">និយ័តករ / អាជ្ញាធរ</option>
                      <option value="DEPARTMENT">អង្គភាពរដ្ឋ / មន្ទីរ</option>
                      <option value="COMPANY">ក្រុមហ៊ុន / វិស័យឯកជន</option>
                      <option value="OTHER">ផ្សេងៗ</option>
                    </select>
                  </div>
                </div>

                <div class="text-right mt-3">
                  <button type="button" class="btn btn-secondary mr-2" @click="manageOrgActiveTab = 'list'">
                    ត្រឡប់ទៅបញ្ជី
                  </button>
                  <button type="submit" class="btn btn-dark-custom px-4" :disabled="savingOrg">
                    <i class="fas fa-spinner fa-spin mr-1" v-if="savingOrg"></i>
                    <i class="fas fa-save mr-1" v-else></i>
                    រក្សាទុកស្ថាប័ន
                  </button>
                </div>
              </form>
            </div>
          </div>

          <div class="modal-footer font-khmer bg-light py-2">
            <button type="button" class="btn btn-secondary btn-sm" @click="closeManageOrgModal">
              បិទ
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Response Content Preview Modal -->
  <Teleport to="body">
    <div class="custom-modal-backdrop font-khmer" v-if="showResponseHtmlModal" @click.self="showResponseHtmlModal = false">
      <div class="modal-dialog modal-lg modal-dialog-centered my-auto" style="width: 100%; max-width: 850px;">
        <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden">
          <div class="modal-header bg-primary text-white py-2 px-3 align-items-center">
            <h6 class="modal-title font-weight-bold mb-0">
              <i class="fas fa-file-alt mr-2 text-warning"></i>ខ្លឹមសារ៖ {{ responseHtmlTitle }}
            </h6>
            <button type="button" class="close text-white" @click="showResponseHtmlModal = false"><span>&times;</span></button>
          </div>
          <div class="modal-body p-4 bg-white font-khmer" style="max-height: 75vh; overflow-y: auto;" v-html="responseHtmlContent"></div>
          <div class="modal-footer py-2 px-3 bg-light justify-content-end">
            <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" @click="showResponseHtmlModal = false">បិទ</button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>

  <!-- Modal 9: Visual Tracking Flow Modal -->
  <DocumentTrackingFlowModal
    v-model:show="showTrackingModal"
    :document-id="trackingDocId"
    @close="showTrackingModal = false"
  />

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import axios from 'axios';
import { useUserStore } from '@/stores/user';
import SignatureModal from '@/components/common/SignatureModal.vue';
import WordEditor from '@/components/common/WordEditor.vue';
import DocumentTrackingFlowModal from '@/components/common/DocumentTrackingFlowModal.vue';
import { getMovementDuration, getApprovalDuration } from '@/utils/durationHelper';
import {
  apiReviewOutboundAction,
  apiGetOutboundDocument,
  apiUpdateOutboundContent,
  apiDeleteOutboundDocument,
} from '@/functions/api/outboundDocument';
import {
  getShortKhmerDateString,
  generateDraggableOpinionBoxHtml,
  embedOpinionBoxIntoHtml,
  setupDraggableOpinionBoxes,
} from '@/utils/opinionBoxHelper';
import {
  apiGetInboundDocuments,
  apiGetInboundDocumentStats,
  apiGetInboundDocument,
  apiGenerateInboundNumber,
  apiGetInboundRecipientsOptions,
  apiCreateInboundDocument,
  apiSendToAssistant,
  apiAssistantReceiveAndSubmitToDg,
  apiDgAnnotate,
  apiAssistantDispatch,
  apiForwardInboundDocument,
  apiAcknowledgeInboundDocument,
  apiGetNextResponseApprovers,
  apiSubmitResponseDraft,
  apiProcessResponseAction,
  apiMarkAssistantReviewed,
  apiRecallInboundDocument,
  apiUpdateInboundDocument,
  apiDeleteInboundDocument,
  apiUpdateTelegramSettings,
  apiTestTelegramConnection,
  apiGetSenderOrganizations,
  apiCreateSenderOrganization,
  apiDeleteSenderOrganization,
  getOriginalDownloadUrl,
  getAnnotatedDownloadUrl,
  getResponseDownloadUrl,
  getViewOriginalUrl,
  getViewAnnotatedUrl,
  getViewResponseUrl
} from '@/functions/api/inboundDocument';

const userStore = useUserStore();
const route = useRoute();
const router = useRouter();

// --- Permissions & Roles ---
const isAdmin = computed(() => userStore.isAdmin);
const canRegister = computed(() => isAdmin.value || userStore.can('inbound-documents-receptionist'));
const canAssist = computed(() => isAdmin.value || userStore.can('inbound-documents-assistant'));
const isDg = computed(() => {
  if (isAdmin.value) return true;
  const level = userStore.position?.level ?? userStore.user?.position?.level;
  return Number(level) === 1;
});
const isRegularOfficer = computed(() => {
  return !isAdmin.value && !canRegister.value && !canAssist.value && !isDg.value;
});

// --- State ---
const loading = ref(false);
const submitting = ref(false);
const activeTab = ref('all');
const detailFlowTab = ref('inbound');
const showResponseHtmlModal = ref(false);
const responseHtmlContent = ref('');
const responseHtmlTitle = ref('');

const previewResponseHtml = (resp) => {
  responseHtmlTitle.value = resp.title || 'ខ្លឹមសារលិខិត';
  responseHtmlContent.value = resp.content || '<p class="text-muted text-center py-4">មិនមានខ្លឹមសារអត្ថបទ</p>';
  showResponseHtmlModal.value = true;
};

const documents = ref({
  data: [],
  total: 0,
  current_page: 1,
  last_page: 1,
  from: 0,
  to: 0,
  prev_page_url: null,
  next_page_url: null,
});

const stats = ref({
  total: 0,
  pending: 0,
  doing: 0,
  completed: 0,
  reception_pending: 0,
  assistant_pending: 0,
  dg_pending: 0,
  dispatched: 0,
  in_response: 0,
  assigned_to_me: 0,
  my_todo: 0,
  my_done: 0,
  my_unit: 0,
  overdue: 0,
  due_soon: 0,
});

const recipientOptions = ref({
  departments: [],
  offices: [],
  users: [],
});

const filters = reactive({
  search: '',
  status: '',
  dg_inbound_category: '',
  urgency: '',
  confidentiality: '',
  deadline_filter: '',
  from_date: '',
  to_date: '',
  page: 1,
});

// Selected Document & Response for Modals
const selectedDoc = ref(null);
const selectedResponse = ref(null);
const responseCandidates = ref([]);
const availableResponseCandidates = computed(() => {
  return (responseCandidates.value || []).filter(c => Number(c.id) !== Number(userStore.id));
});

// Modals display states
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingDoc = ref(false);
const selectedEditDoc = ref(null);
const editForm = reactive({
  general_inbound_number: '',
  received_date: '',
  received_time: '',
  deliverer_name: '',
  deliverer_phone: '',
  sender_organization: '',
  external_reference_number: '',
  external_document_date: '',
  deadline: '',
  title: '',
  document_type: 'LETTER',
  urgency: 'NORMAL',
  confidentiality: 'NORMAL',
  receptionist_notes: '',
  original_file: null,
});
const showAssistantReceiveModal = ref(false);
const showDgAnnotateModal = ref(false);
const selectedDgAnnotatedFile = ref(null);
const selectedDgAnnotatedFileName = ref('');
const handleDgAnnotatedFileChange = (e) => {
  const file = e.target.files?.[0];
  if (file) {
    selectedDgAnnotatedFile.value = file;
    selectedDgAnnotatedFileName.value = file.name;
  } else {
    selectedDgAnnotatedFile.value = null;
    selectedDgAnnotatedFileName.value = '';
  }
};
const showForwardModal = ref(false);
const forwarding = ref(false);
const showSubmitResponseModal = ref(false);
const showReviewResponseModal = ref(false);
const showDetailModal = ref(false);
const showTelegramModal = ref(false);
const testingTelegram = ref(false);
const savingTelegram = ref(false);
const telegramForm = reactive({
  chat_id: '',
  username: '',
});

// --- PDF Viewer Modal State ---
const showPdfModal = ref(false);
const pdfModalTitle = ref('');
const pdfModalUrl = ref('');
const pdfModalDownloadUrl = ref('');
const isPdfLoading = ref(true);
const pdfError = ref(false);

const openPdfPreview = async (title, viewUrl, downloadUrl) => {
  pdfModalTitle.value = title || 'មើលឯកសារ PDF';
  pdfModalDownloadUrl.value = downloadUrl || viewUrl;
  isPdfLoading.value = true;
  pdfError.value = false;
  showPdfModal.value = true;

  if (pdfModalUrl.value && pdfModalUrl.value.startsWith('blob:')) {
    URL.revokeObjectURL(pdfModalUrl.value);
  }
  pdfModalUrl.value = '';

  try {
    const res = await axios.get(viewUrl, { responseType: 'blob' });
    const blob = new Blob([res.data], { type: 'application/pdf' });
    pdfModalUrl.value = URL.createObjectURL(blob);
  } catch (err) {
    console.error('PDF fetch failed:', err);
    pdfError.value = true;
    pdfModalUrl.value = viewUrl;
  } finally {
    isPdfLoading.value = false;
  }
};

const closePdfModal = () => {
  showPdfModal.value = false;
  if (pdfModalUrl.value && pdfModalUrl.value.startsWith('blob:')) {
    URL.revokeObjectURL(pdfModalUrl.value);
  }
  pdfModalUrl.value = '';
};

// --- Sender Organizations State ---
const senderOrganizations = ref([]);
const senderOrgSelection = ref('');
const customSenderOrg = ref('');
const orgSearchQuery = ref('');
const isOrgDropdownOpen = ref(false);

const filteredSenderOrganizations = computed(() => {
  const q = orgSearchQuery.value.trim().toLowerCase();
  if (!q) return senderOrganizations.value;
  return senderOrganizations.value.filter(org => {
    const kh = (org.name_kh || '').toLowerCase();
    const en = (org.name_en || '').toLowerCase();
    const code = (org.code || '').toLowerCase();
    return kh.includes(q) || en.includes(q) || code.includes(q);
  });
});

const toggleOrgDropdown = () => {
  isOrgDropdownOpen.value = !isOrgDropdownOpen.value;
  if (isOrgDropdownOpen.value) {
    orgSearchQuery.value = '';
  }
};

const selectOrg = (org) => {
  senderOrgSelection.value = org.name_kh;
  createForm.sender_organization = org.name_kh;
  isOrgDropdownOpen.value = false;
  orgSearchQuery.value = '';
};

const selectOtherOrg = () => {
  senderOrgSelection.value = '__OTHER__';
  createForm.sender_organization = customSenderOrg.value;
  isOrgDropdownOpen.value = false;
  orgSearchQuery.value = '';
};

const clearSelectedOrg = () => {
  senderOrgSelection.value = '';
  createForm.sender_organization = '';
  customSenderOrg.value = '';
  orgSearchQuery.value = '';
};

const useQueryAsCustomOrg = () => {
  customSenderOrg.value = orgSearchQuery.value.trim();
  senderOrgSelection.value = '__OTHER__';
  createForm.sender_organization = customSenderOrg.value;
  isOrgDropdownOpen.value = false;
  orgSearchQuery.value = '';
};

// --- Manage Sender Organizations Modal State ---
const showManageOrgModal = ref(false);
const manageOrgActiveTab = ref('list'); // 'list' | 'create'
const manageOrgSearch = ref('');
const savingOrg = ref(false);
const newOrgForm = reactive({
  name_kh: '',
  name_en: '',
  code: '',
  category: 'OTHER',
});

const filteredManageOrgs = computed(() => {
  const q = manageOrgSearch.value.trim().toLowerCase();
  if (!q) return senderOrganizations.value;
  return senderOrganizations.value.filter(org => {
    const kh = (org.name_kh || '').toLowerCase();
    const en = (org.name_en || '').toLowerCase();
    const code = (org.code || '').toLowerCase();
    return kh.includes(q) || en.includes(q) || code.includes(q);
  });
});

const getCategoryLabelKh = (cat) => {
  switch (cat) {
    case 'MINISTRY': return 'ក្រសួង';
    case 'REGULATOR': return 'និយ័តករ';
    case 'DEPARTMENT': return 'អង្គភាពរដ្ឋ';
    case 'COMPANY': return 'ក្រុមហ៊ុន';
    default: return 'ផ្សេងៗ';
  }
};

const loadSenderOrganizations = async () => {
  try {
    const res = await apiGetSenderOrganizations();
    senderOrganizations.value = res.data;
  } catch (err) {
    console.error('Failed to load sender organizations:', err);
  }
};

const openManageOrgModal = () => {
  manageOrgActiveTab.value = 'list';
  manageOrgSearch.value = '';
  newOrgForm.name_kh = '';
  newOrgForm.name_en = '';
  newOrgForm.code = '';
  newOrgForm.category = 'OTHER';
  showManageOrgModal.value = true;
};

const closeManageOrgModal = () => {
  showManageOrgModal.value = false;
};

const confirmDeleteOrg = async (org) => {
  const confirm = await Swal.fire({
    title: 'លុបស្ថាប័ននេះ?',
    text: `តើលោកអ្នកពិតជាចង់លុប "${org.name_kh}" ចេញពីបញ្ជីកំណត់ទុកជាមុនមែនទេ?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'បាទ/ចាស លុបចេញ',
    cancelButtonText: 'បោះបង់'
  });
  if (!confirm.isConfirmed) return;

  try {
    await apiDeleteSenderOrganization(org.id);
    Swal.fire({
      icon: 'success',
      title: 'បានលុបជោគជ័យ!',
      text: `បានលុបស្ថាប័ន "${org.name_kh}" រួចរាល់!`,
      timer: 1500,
      showConfirmButton: false,
    });
    await loadSenderOrganizations();
    if (senderOrgSelection.value === org.name_kh) {
      senderOrgSelection.value = '';
      createForm.sender_organization = '';
    }
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ!',
      text: err.response?.data?.message || 'មិនអាចលុបស្ថាប័ននេះបានឡើយ!',
      confirmButtonText: 'យល់ព្រម',
    });
  }
};

const submitCreateSenderOrg = async () => {
  if (!newOrgForm.name_kh.trim()) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមបញ្ចូលឈ្មោះស្ថាប័ន!',
      text: 'សូមបញ្ចូលឈ្មោះស្ថាប័ន ឬអង្គភាពជាភាសាខ្មែរ',
      confirmButtonText: 'យល់ព្រម',
    });
    return;
  }
  savingOrg.value = true;
  try {
    const res = await apiCreateSenderOrganization({
      name_kh: newOrgForm.name_kh.trim(),
      name_en: newOrgForm.name_en ? newOrgForm.name_en.trim() : null,
      code: newOrgForm.code ? newOrgForm.code.trim() : null,
      category: newOrgForm.category || 'OTHER',
    });
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: res.data.message || 'បានបន្ថែមស្ថាប័នដោយជោគជ័យ!',
      confirmButtonText: 'យល់ព្រម',
      timer: 1500,
      showConfirmButton: false,
    });
    await loadSenderOrganizations();
    senderOrgSelection.value = res.data.organization?.name_kh || newOrgForm.name_kh.trim();
    createForm.sender_organization = senderOrgSelection.value;
    manageOrgActiveTab.value = 'list';
    newOrgForm.name_kh = '';
    newOrgForm.name_en = '';
    newOrgForm.code = '';
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ!',
      text: err.response?.data?.message || 'មិនអាចបន្ថែមស្ថាប័នបានឡើយ!',
      confirmButtonText: 'យល់ព្រម',
    });
  } finally {
    savingOrg.value = false;
  }
};

const handleOrgSelectionChange = () => {
  if (senderOrgSelection.value === '__OTHER__') {
    createForm.sender_organization = customSenderOrg.value;
  } else {
    createForm.sender_organization = senderOrgSelection.value;
  }
};

// Files selected
const selectedOriginalFile = ref(null);
const selectedOriginalFileName = ref('');
const selectedAnnotatedFile = ref(null);
const selectedAnnotatedFileName = ref('');
const selectedResponseFile = ref(null);
const selectedResponseFileName = ref('');

// --- Forms ---
const createForm = reactive({
  general_inbound_number: '',
  received_date: new Date().toISOString().substring(0, 10),
  received_time: new Date().toTimeString().substring(0, 5),
  deliverer_name: '',
  deliverer_phone: '',
  sender_organization: '',
  external_reference_number: '',
  external_document_date: '',
  deadline: '',
  title: '',
  document_type: 'LETTER',
  urgency: 'NORMAL',
  confidentiality: 'NORMAL',
  receptionist_notes: '',
  send_immediately: true,
});

const assistantReceiveForm = reactive({
  dg_inbound_category: 'COMPANY',
  custom_dg_number: '',
  dg_received_date: new Date().toISOString().substring(0, 10),
  dg_assistant_notes: '',
});

const officerDgSearch = ref('');
const filteredDgOfficerOptions = computed(() => {
  const users = recipientOptions.value.users || [];
  if (!officerDgSearch.value.trim()) return users;
  const q = officerDgSearch.value.toLowerCase().trim();
  return users.filter(u => {
    const nameKh = (u.name_kh || '').toLowerCase();
    const name = (u.name || '').toLowerCase();
    const pos = (u.position?.title_kh || '').toLowerCase();
    const dept = (u.department?.name_kh || '').toLowerCase();
    return nameKh.includes(q) || name.includes(q) || pos.includes(q) || dept.includes(q);
  });
});

const dgAnnotateForm = reactive({
  dg_annotation: '',
  is_response_required: false,
  deadline: '',
  department_ids: [],
  office_ids: [],
  user_ids: [],
  working_group_ids: [],
  dispatch_notes: '',
});

const dispatchForm = reactive({
  target_type: 'MULTIPLE',
  target_department_id: '',
  target_office_id: '',
  target_user_id: '',
  department_ids: [],
  office_ids: [],
  user_ids: [],
  working_group_ids: [],
  dispatch_notes: '',
});

const responseForm = reactive({
  title: '',
  response_type: 'REPORT',
  header_organization: '',
  reference_note: '',
  content: '',
  sign_title: '',
  sign_name: '',
  forwarded_to_id: '',
  comment: '',
});

const showInboundReviewSignatureModal = ref(false);
const submittingReview = ref(false);
const responseReviewStep = ref('form'); // 'form' | 'position'
const responseReviewWorkingHtml = ref('');
const responseReviewPositionPaperRef = ref(null);
let cleanupResponseReviewDraggable = null;

const khmerDigits = { '0': '០', '1': '១', '2': '២', '3': '៣', '4': '៤', '5': '៥', '6': '៦', '7': '៧', '8': '៨', '9': '៩' };
const toKhmerNum = (num) => String(num).split('').map(d => khmerDigits[d] || d).join('');
const getCurrentKhmerDateString = () => {
  const d = new Date();
  const day = toKhmerNum(d.getDate());
  const monthNamesKh = ['មករា', 'កុម្ភៈ', 'មីនា', 'មេសា', 'ឧសភា', 'មិថុនា', 'កក្កដា', 'សីហា', 'កញ្ញា', 'តុលា', 'វិច្ឆិកា', 'ធ្នូ'];
  const month = monthNamesKh[d.getMonth()];
  const year = toKhmerNum(d.getFullYear());
  return `រាជធានីភ្នំពេញ, ថ្ងៃទី${day} ខែ${month} ឆ្នាំ${year}`;
};

const reviewActionForm = reactive({
  action: 'FORWARD',
  forwarded_to_id: '',
  response_number: '',
  comment: '',
  embed_into_document: true,
  placement: 'BOTTOM',
  sign_date: '',
});

const forwardForm = reactive({
  target_type: 'OFFICER',
  target_department_id: '',
  target_office_id: '',
  target_user_id: '',
  target_working_group_id: '',
  user_ids: [],
  forwarding_notes: '',
});

const showTrackingModal = ref(false);
const trackingDocId = ref(null);

const openTrackingModal = (doc) => {
  trackingDocId.value = doc.id;
  showTrackingModal.value = true;
};

// --- Working Group Helpers ---
const isUserInWorkingGroup = (wgId) => {
  if (!recipientOptions.value?.working_groups) return false;
  const wg = recipientOptions.value.working_groups.find(g => g.id === wgId);
  if (!wg || !wg.members) return false;
  return wg.members.some(m => m.user_id === userStore.id && m.status === 'ACTIVE');
};

const isUserLeaderOfWorkingGroup = (wgId) => {
  if (!recipientOptions.value?.working_groups) return false;
  const wg = recipientOptions.value.working_groups.find(g => g.id === wgId);
  if (!wg || !wg.members) return false;
  return wg.members.some(m => m.user_id === userStore.id && ['LEADER', 'DEPUTY_LEADER'].includes(m.role) && m.status === 'ACTIVE');
};

const isUserAssignedViaWorkingGroup = (doc) => {
  if (!doc?.assignments) return false;
  return doc.assignments.some(a => a.working_group_id && isUserInWorkingGroup(a.working_group_id));
};

const isUserLeaderOfAssignedWorkingGroup = (doc) => {
  if (!doc?.assignments) return false;
  return doc.assignments.some(a => a.working_group_id && isUserLeaderOfWorkingGroup(a.working_group_id));
};

// --- Computed Helpers ---
const filteredDispatchOffices = computed(() => {
  if (!dispatchForm.target_department_id) return recipientOptions.value.offices;
  return recipientOptions.value.offices.filter(o => o.department_id === Number(dispatchForm.target_department_id));
});

const filteredDispatchUsers = computed(() => {
  let list = recipientOptions.value.users;
  if (dispatchForm.target_department_id) {
    list = list.filter(u => u.department_id === Number(dispatchForm.target_department_id));
  }
  if (dispatchForm.target_office_id) {
    list = list.filter(u => u.office_id === Number(dispatchForm.target_office_id));
  }
  return list;
});

const officerDispatchSearch = ref('');
const filteredOfficerOptions = computed(() => {
  const users = recipientOptions.value.users || [];
  if (!officerDispatchSearch.value.trim()) return users;
  const q = officerDispatchSearch.value.toLowerCase().trim();
  return users.filter(u => {
    const nameKh = (u.name_kh || '').toLowerCase();
    const name = (u.name || '').toLowerCase();
    const pos = (u.position?.title_kh || '').toLowerCase();
    const dept = (u.department?.name_kh || '').toLowerCase();
    return nameKh.includes(q) || name.includes(q) || pos.includes(q) || dept.includes(q);
  });
});

// --- Dynamic Forwarding Target Options & Subordinates Filters ---
const myWfConfig = computed(() => recipientOptions.value?.my_workflow_config || null);

const forwardTargetTypeOptions = computed(() => {
  const isSuper = isAdmin.value || (userStore.position && Number(userStore.position.level) <= 2) || canAssist.value;
  if (isSuper) {
    return [
      { value: 'DEPARTMENT', label: 'នាយកដ្ឋាន', icon: 'fas fa-building text-secondary' },
      { value: 'OFFICE', label: 'ការិយាល័យ', icon: 'fas fa-door-closed text-info' },
      { value: 'OFFICER', label: 'មន្ត្រីជាក់លាក់', icon: 'fas fa-user-circle text-primary' },
      { value: 'WORKING_GROUP', label: 'ក្រុមការងារ', icon: 'fas fa-users-cog text-indigo' },
    ];
  }

  // 1. Check if user has explicit Workflow Sending Group configuration
  if (myWfConfig.value) {
    const st = myWfConfig.value.send_type;
    if (st === 'ALL') {
      return [
        { value: 'DEPARTMENT', label: 'នាយកដ្ឋាន', icon: 'fas fa-building text-secondary' },
        { value: 'OFFICE', label: 'ការិយាល័យ', icon: 'fas fa-door-closed text-info' },
        { value: 'OFFICER', label: 'មន្ត្រីជាក់លាក់', icon: 'fas fa-user-circle text-primary' },
        { value: 'WORKING_GROUP', label: 'ក្រុមការងារ', icon: 'fas fa-users-cog text-indigo' },
      ];
    }
    if (st === 'DEPARTMENT') {
      return [
        { value: 'OFFICE', label: 'ការិយាល័យក្នុងនាយកដ្ឋាន', icon: 'fas fa-door-closed text-info' },
        { value: 'OFFICER', label: 'មន្ត្រីក្នុងនាយកដ្ឋាន', icon: 'fas fa-user-circle text-primary' },
      ];
    }
    if (st === 'OFFICE') {
      return [
        { value: 'OFFICER', label: 'មន្ត្រីក្នុងការិយាល័យ', icon: 'fas fa-user-circle text-primary' },
      ];
    }
    if (st === 'SUBORDINATES') {
      return [
        { value: 'OFFICER', label: 'មន្ត្រីក្រោមឱវាទ', icon: 'fas fa-user-circle text-primary' },
      ];
    }
    if (st === 'CUSTOM') {
      const customOpts = [];
      if (myWfConfig.value.send_dept_ids?.length) {
        customOpts.push({ value: 'DEPARTMENT', label: 'នាយកដ្ឋានគោលដៅ (Custom)', icon: 'fas fa-building text-secondary' });
      }
      if (myWfConfig.value.send_office_ids?.length) {
        customOpts.push({ value: 'OFFICE', label: 'ការិយាល័យគោលដៅ (Custom)', icon: 'fas fa-door-closed text-info' });
      }
      if (myWfConfig.value.send_user_ids?.length) {
        customOpts.push({ value: 'OFFICER', label: 'មន្ត្រីគោលដៅ (Custom)', icon: 'fas fa-user-circle text-primary' });
      }
      if (myWfConfig.value.send_working_group_ids?.length) {
        customOpts.push({ value: 'WORKING_GROUP', label: 'ក្រុមការងារគោលដៅ (Custom)', icon: 'fas fa-users-cog text-indigo' });
      }
      if (customOpts.length > 0) return customOpts;
    }
  }

  const userPosLevel = userStore.position ? Number(userStore.position.level) : 999;
  const isDeptDirector = userPosLevel <= 4;
  const isDeptLeader = userPosLevel <= 5;
  const isOfficeLeader = userPosLevel <= 8;
  const isWgLeader = isUserLeaderOfAssignedWorkingGroup(selectedDoc.value);

  const opts = [];
  if (isDeptLeader) {
    opts.push({ value: 'OFFICE', label: 'ការិយាល័យក្រោមឱវាទ', icon: 'fas fa-door-closed text-info' });
    opts.push({ value: 'OFFICER', label: 'មន្ត្រីក្រោមឱវាទក្នុងនាយកដ្ឋាន', icon: 'fas fa-user-circle text-primary' });
  } else if (isOfficeLeader) {
    opts.push({ value: 'OFFICER', label: 'មន្ត្រីក្រោមឱវាទក្នុងការិយាល័យ', icon: 'fas fa-user-circle text-primary' });
  }

  if (isWgLeader) {
    opts.push({ value: 'WG_MEMBER', label: 'សមាជិកក្នុងក្រុមការងារ', icon: 'fas fa-users text-indigo' });
  }

  if (opts.length === 0) {
    opts.push({ value: 'OFFICER', label: 'មន្ត្រីជាក់លាក់', icon: 'fas fa-user-circle text-primary' });
  }
  return opts;
});

const availableForwardOffices = computed(() => {
  const offices = recipientOptions.value.offices || [];
  const isSuper = isAdmin.value || (userStore.position && Number(userStore.position.level) <= 2) || canAssist.value;
  if (isSuper) return offices;

  const userLevel = userStore.position ? Number(userStore.position.level) : 999;
  const isDeptLeader = userLevel <= 5;

  if (myWfConfig.value) {
    if (myWfConfig.value.send_type === 'ALL') return offices;
    if (myWfConfig.value.send_type === 'CUSTOM') {
      const allowed = (myWfConfig.value.send_office_ids || []).map(Number);
      return offices.filter(o => allowed.includes(Number(o.id)));
    }
    if ((myWfConfig.value.send_type === 'DEPARTMENT' || (myWfConfig.value.send_type === 'SUBORDINATES' && isDeptLeader)) && userStore.department_id) {
      return offices.filter(o => Number(o.department_id) === Number(userStore.department_id));
    }
  }

  if (isDeptLeader && userStore.department_id) {
    return offices.filter(o => Number(o.department_id) === Number(userStore.department_id));
  }
  return [];
});

const availableForwardOfficers = computed(() => {
  const users = recipientOptions.value.users || [];
  const isSuper = isAdmin.value || (userStore.position && Number(userStore.position.level) <= 2) || canAssist.value;
  if (isSuper) {
    return users;
  }

  const userPosLevel = userStore.position ? Number(userStore.position.level) : 999;
  const isDeptLeader = userPosLevel <= 5;
  const isOfficeLeader = userPosLevel <= 8;
  const targetType = forwardForm.target_type;

  // 1. Check explicit Workflow Sending Group configuration
  if (myWfConfig.value) {
    if (myWfConfig.value.send_type === 'ALL') {
      return users.filter(u => Number(u.id) !== Number(userStore.id));
    }
    if (myWfConfig.value.send_type === 'CUSTOM') {
      const allowed = (myWfConfig.value.send_user_ids || []).map(Number);
      return users.filter(u => allowed.includes(Number(u.id)) && Number(u.id) !== Number(userStore.id));
    }
    if (myWfConfig.value.send_type === 'DEPARTMENT') {
      return users.filter(u => Number(u.department_id) === Number(userStore.department_id) && Number(u.id) !== Number(userStore.id));
    }
    if (myWfConfig.value.send_type === 'OFFICE') {
      return users.filter(u => Number(u.office_id) === Number(userStore.office_id) && Number(u.id) !== Number(userStore.id));
    }
    if (myWfConfig.value.send_type === 'SUBORDINATES') {
      return users.filter(u => {
        const uPosLevel = u.position ? Number(u.position.level) : 999;
        const sameOffice = userStore.office_id && Number(u.office_id) === Number(userStore.office_id);
        const sameDept = userStore.department_id && Number(u.department_id) === Number(userStore.department_id);
        return (sameOffice || sameDept) && Number(u.id) !== Number(userStore.id) && uPosLevel > userPosLevel;
      });
    }
  }

  // 2. Default hierarchy fallbacks

  // If WG_MEMBER selected or user is forwarding within WG
  if (targetType === 'WG_MEMBER' || (isUserLeaderOfAssignedWorkingGroup(selectedDoc.value) && targetType === 'OFFICER' && !isDeptLeader && !isOfficeLeader)) {
    const assignedWgIds = (selectedDoc.value?.assignments || [])
      .filter(a => a.working_group_id)
      .map(a => a.working_group_id);
    const memberUserIds = new Set();
    (recipientOptions.value.working_groups || [])
      .filter(wg => assignedWgIds.includes(wg.id))
      .forEach(wg => {
        (wg.members || []).forEach(m => {
          if (m.status === 'ACTIVE' && Number(m.user_id) !== Number(userStore.id)) {
            memberUserIds.add(Number(m.user_id));
          }
        });
      });
    return users.filter(u => memberUserIds.has(Number(u.id)));
  }

  // Office leadership: only subordinates in the same office with lower position level
  if (isOfficeLeader && !isDeptLeader) {
    return users.filter(u => {
      const uPosLevel = u.position ? Number(u.position.level) : 999;
      return Number(u.office_id) === Number(userStore.office_id) && Number(u.id) !== Number(userStore.id) && uPosLevel > userPosLevel;
    });
  }

  // Department leadership: subordinates in the same department with lower position level
  if (isDeptLeader) {
    return users.filter(u => {
      const uPosLevel = u.position ? Number(u.position.level) : 999;
      return Number(u.department_id) === Number(userStore.department_id) && Number(u.id) !== Number(userStore.id) && uPosLevel > userPosLevel;
    });
  }

  // Any other assigned officer forwarding down to lower subordinates in same office or department
  return users.filter(u => {
    const uPosLevel = u.position ? Number(u.position.level) : 999;
    const sameOffice = userStore.office_id && Number(u.office_id) === Number(userStore.office_id);
    const sameDept = userStore.department_id && Number(u.department_id) === Number(userStore.department_id);
    return (sameOffice || sameDept) && Number(u.id) !== Number(userStore.id) && uPosLevel > userPosLevel;
  });
});

const officerForwardSearch = ref('');
const filteredForwardOfficers = computed(() => {
  const officers = availableForwardOfficers.value;
  if (!officerForwardSearch.value.trim()) return officers;
  const q = officerForwardSearch.value.toLowerCase().trim();
  return officers.filter(u => {
    const nameKh = (u.name_kh || '').toLowerCase();
    const name = (u.name || '').toLowerCase();
    const pos = (u.position?.title_kh || '').toLowerCase();
    const off = (u.office?.name_kh || '').toLowerCase();
    return nameKh.includes(q) || name.includes(q) || pos.includes(q) || off.includes(q);
  });
});

const paginationPages = computed(() => {
  const current = documents.value.current_page || 1;
  const last = documents.value.last_page || 1;
  const pages = [];
  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i);
  }
  return pages;
});

// --- Actions Checkers ---
const hasUserForwarded = (doc) => {
  if (!doc) return false;
  const userId = Number(userStore.id);
  // Check if current user has an active forwarded assignment
  const hasActiveForward = doc.assignments?.some(a => 
    Number(a.assigned_by || a.assigned_by_user_id || a.assigned_by_user?.id) === userId && a.status !== 'RECALLED'
  );
  if (hasActiveForward) return true;

  // Also check movements if present
  const hasForwardMovement = doc.movements?.some(m => 
    Number(m.user_id) === userId && m.action === 'FORWARDED'
  );
  return !!hasForwardMovement;
};

const canAcknowledge = (doc) => {
  if (!doc) return false;
  if (doc.status !== 'DISPATCHED' || doc.is_response_required) return false;
  if (['COMPLETED', 'CANCELLED'].includes(doc.status)) return false;
  if (doc.acknowledged_at || doc.acknowledged_by) return false;
  if (hasUserForwarded(doc)) return false; // Point 3: already forwarded!

  const userId = Number(userStore.id);
  const userDeptId = userStore.department_id ? Number(userStore.department_id) : null;
  const userOfficeId = userStore.office_id ? Number(userStore.office_id) : null;
  const userPosLevel = userStore.position?.level != null ? Number(userStore.position.level) : 999;

  if (isAdmin.value) return true;

  // 1. If targeted specifically to this user
  if (doc.target_user_id && Number(doc.target_user_id) === userId) {
    return true;
  }
  // 2. Direct pending assignment to user
  if (doc.assignments?.some(a => Number(a.user_id || a.user?.id) === userId && a.status === 'PENDING')) {
    return true;
  }
  // 3. If targeted to office/department and not yet forwarded to specific officers
  const hasOfficerAssignments = doc.assignments?.some(a => a.target_type === 'USER' && a.status !== 'RECALLED');
  if (!hasOfficerAssignments) {
    if (doc.target_office_id && userOfficeId && Number(doc.target_office_id) === userOfficeId) {
      return userPosLevel <= 8;
    }
    if (doc.target_department_id && userDeptId && Number(doc.target_department_id) === userDeptId) {
      return userPosLevel <= 5;
    }
  }
  return false;
};

const canCreateReport = (doc) => {
  if (!doc) return false;
  if (!['DISPATCHED', 'IN_RESPONSE_PROGRESS'].includes(doc.status)) return false;
  if (['COMPLETED', 'CANCELLED'].includes(doc.status)) return false;
  if (isDg.value) return false; // DG does not draft reports
  if (hasUserForwarded(doc)) return false; // Point 3: already forwarded!

  // If a response is already drafted/in review, cannot create another duplicate response
  if (doc.status === 'IN_RESPONSE_PROGRESS' || doc.latest_response || (doc.responses && doc.responses.length > 0)) {
    return false;
  }

  const userId = Number(userStore.id);
  const userDeptId = userStore.department_id ? Number(userStore.department_id) : null;
  const userOfficeId = userStore.office_id ? Number(userStore.office_id) : null;
  const userPosLevel = userStore.position?.level != null ? Number(userStore.position.level) : 999;

  // 1. Direct assignment to this user
  const docUserId = Number(doc.target_user_id || doc.target_user?.id);
  if (docUserId === userId || doc.assignments?.some(a => Number(a.user_id || a.user?.id) === userId)) {
    return true;
  }

  // 2. Working group assigned
  if (isUserAssignedViaWorkingGroup(doc) || isUserLeaderOfAssignedWorkingGroup(doc)) {
    return true;
  }

  // 3. If assigned to office without specific officer yet, Office Chief/officers can draft
  const hasOfficerAssignments = doc.assignments?.some(a => a.target_type === 'USER' && a.status !== 'RECALLED');
  if (!hasOfficerAssignments) {
    const docOfficeId = Number(doc.target_office_id || doc.target_office?.id);
    if (userOfficeId && (docOfficeId === userOfficeId || doc.assignments?.some(a => Number(a.office_id || a.office?.id) === userOfficeId))) {
      return true;
    }

    // 4. If assigned to department without office yet
    const docDeptId = Number(doc.target_department_id || doc.target_department?.id);
    if (userDeptId && (docDeptId === userDeptId || doc.assignments?.some(a => Number(a.department_id || a.department?.id) === userDeptId))) {
      return true;
    }
  }

  return false;
};

const canDraftResponse = (doc) => canCreateReport(doc);

const canReviewResponse = (doc) => {
  const latest = doc.latest_response;
  if (!latest || latest.status !== 'UNDER_REVIEW') return false;
  return isAdmin.value || latest.current_approver_id === userStore.id;
};

const canDeleteResponse = (resp) => {
  if (!resp) return false;
  const isDrafter = (resp.drafted_by === userStore.id || resp.drafted_by_user?.id === userStore.id);
  const isAdminUser = (userStore.level && userStore.level.toUpperCase() === 'ADMIN') || isAdmin.value;
  if (!isDrafter && !isAdminUser) return false;
  return ['DRAFT', 'RETURNED'].includes(resp.status) || isAdminUser;
};

const deleteResponseAction = async (resp) => {
  const isReturned = (resp.status === 'RETURNED');
  const docTypeLabel = isReturned ? 'ឯកសារដែលថ្នាក់ដឹកនាំបានបញ្ជូនត្រឡប់មកវិញនេះ' : 'សេចក្តីព្រាងឆ្លើយតបនេះ';
  const result = await Swal.fire({
    title: 'តើលោកអ្នកពិតជាចង់លុបមែនទេ?',
    text: `${docTypeLabel} នឹងត្រូវលុបចេញពីប្រព័ន្ធទាំងស្រុង!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    confirmButtonText: 'យល់ព្រមលុប',
    cancelButtonText: 'បោះបង់',
  });
  if (!result.isConfirmed) return;

  try {
    await apiDeleteOutboundDocument(resp.id);
    Swal.fire({ icon: 'success', title: 'បានលុបជោគជ័យ!', timer: 1500, showConfirmButton: false });
    if (selectedDoc.value?.id) {
      const res = await apiGetInboundDocument(selectedDoc.value.id);
      if (res.data?.data) {
        selectedDoc.value = res.data.data;
      }
    }
    loadDocuments();
    loadStats();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចលុបបានឡើយ!' });
  }
};

const canRecallInbound = (doc) => {
  if (!doc || doc.status === 'COMPLETED' || doc.status === 'CANCELLED') return false;
  const userId = Number(userStore.id);
  if (isDg.value && doc.dispatched_by === userId) return true;
  return doc.assignments?.some(a => Number(a.assigned_by || a.assigned_by_user_id) === userId && a.status === 'PENDING');
};

const markAssistantReviewedAction = async (doc) => {
  const result = await Swal.fire({
    title: 'បានពិនិត្យរួចរាល់?',
    text: `តើលោកអ្នកបានពិនិត្យឯកសារលេខ ${doc.general_inbound_number || doc.id} រួចរាល់ហើយមែនទេ?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'យល់ព្រម',
    cancelButtonText: 'បោះបង់',
  });
  if (result.isConfirmed) {
    try {
      await apiMarkAssistantReviewed(doc.id);
      Swal.fire('ជោគជ័យ', 'បានកត់ត្រាការពិនិត្យរួចរាល់!', 'success');
      loadDocuments();
      loadStats();
    } catch (err) {
      Swal.fire('បរាជ័យ', err.response?.data?.message || 'មានបញ្ហា!', 'error');
    }
  }
};

const recallInboundAction = async (doc) => {
  const result = await Swal.fire({
    title: 'ទាញយកមកវិញ (Recall)?',
    text: `តើលោកអ្នកចង់ទាញយកការចាត់ចែងលើឯកសារលេខ ${doc.general_inbound_number || doc.id} មកវិញមែនទេ?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'យល់ព្រមទាញយក',
    cancelButtonText: 'បោះបង់',
    confirmButtonColor: '#d33',
  });
  if (result.isConfirmed) {
    try {
      await apiRecallInboundDocument(doc.id);
      Swal.fire('ជោគជ័យ', 'បានទាញយកមកវិញដោយជោគជ័យ!', 'success');
      loadDocuments();
      loadStats();
    } catch (err) {
      Swal.fire('បរាជ័យ', err.response?.data?.message || 'មិនអាចទាញយកមកវិញបានទេ!', 'error');
    }
  }
};

const canForward = (doc) => {
  if (!doc) return false;
  if (['COMPLETED', 'CANCELLED'].includes(doc.status)) return false;
  if (['RECEPTION_DRAFT', 'SUBMITTED_TO_ASSISTANT', 'SUBMITTED_TO_DG', 'DG_ANNOTATED'].includes(doc.status)) return false;
  if (isDg.value) return false; // DG dispatches via DG Annotation modal, so DG does not use cascading forward button
  if (hasUserForwarded(doc)) return false; // Point 3: already forwarded!

  const userId = Number(userStore.id);
  const userPos = userStore.position;
  const userLevel = (userPos && userPos.level != null) ? Number(userPos.level) : (userStore.level ? Number(userStore.level) : 999);
  const userDeptId = (userStore.department_id || userStore.department?.id) ? Number(userStore.department_id || userStore.department?.id) : null;
  const userOfficeId = (userStore.office_id || userStore.office?.id) ? Number(userStore.office_id || userStore.office?.id) : null;

  // 1. User specifically assigned with PENDING status
  const docUserId = Number(doc.target_user_id || doc.target_user?.id);
  if (docUserId === userId) return true;
  if (doc.assignments?.some(a => Number(a.user_id || a.user?.id) === userId && a.status === 'PENDING')) return true;

  // 2. Working Group leader / deputy leader
  if (isUserLeaderOfAssignedWorkingGroup(doc)) {
    return true;
  }

  // 3. Office leadership (Level <= 8): can forward if doc is targeted to this office and not yet forwarded to officers
  const hasOfficerAssignments = doc.assignments?.some(a => a.target_type === 'USER' && a.status !== 'RECALLED');
  if (userLevel <= 8 && userOfficeId && !hasOfficerAssignments) {
    const docOfficeId = Number(doc.target_office_id || doc.target_office?.id);
    if (docOfficeId === userOfficeId || doc.assignments?.some(a => Number(a.office_id || a.office?.id) === userOfficeId)) {
      return true;
    }
  }

  // 4. Department Leadership (Level <= 5): can forward if doc is in their department and not yet forwarded downwards
  const hasSubAssignments = doc.assignments?.some(a => (a.target_type === 'OFFICE' || a.target_type === 'USER') && a.status !== 'RECALLED');
  if (userLevel <= 5 && userDeptId && !hasSubAssignments) {
    const docDeptId = Number(doc.target_department_id || doc.target_department?.id);
    if (docDeptId === userDeptId || doc.assignments?.some(a => Number(a.department_id || a.department?.id) === userDeptId)) {
      return true;
    }
  }

  return false;
};

// សិទ្ធិលុបឯកសារចូល (ករណីបញ្ចូលខុស)
// - អគ្គនាយក ឬ Admin អាចលុបបានគ្រប់ពេល
// - អ្នកទទួល ឬជំនួយការ ករណីមិនទាន់បញ្ជូនទៅអគ្គនាយក អាចលុបចេញបាន
const canDeleteDocument = (doc) => {
  if (!doc) return false;
  // 1. អគ្គនាយក ឬ Admin
  if (isAdmin.value || isDg.value) return true;

  // 2. អ្នកទទួល ឬជំនួយការ ឬអ្នកដែលបានចុះឈ្មោះផ្ទាល់ (ប្រសិនបើមិនទាន់បញ្ជូនទៅអគ្គនាយក)
  const notYetSubmittedToDg = ['RECEPTION_DRAFT', 'SUBMITTED_TO_ASSISTANT'].includes(doc.status);
  const isReceptionOrAssistant = canRegister.value || canAssist.value || (doc.registered_by === userStore.id);

  return notYetSubmittedToDg && isReceptionOrAssistant;
};

// សិទ្ធិកែប្រែព័ត៌មានឯកសារចូល (ករណីបញ្ចូលខុស)
// - អគ្គនាយក ឬ Admin អាចកែប្រែបានគ្រប់ពេល
// - អ្នកទទួល ឬជំនួយការ ករណីមិនទាន់បញ្ជូនទៅអគ្គនាយក អាចកែប្រែបាន
const canEditDocument = (doc) => {
  if (!doc) return false;
  if (isAdmin.value || isDg.value) return true;

  const notYetSubmittedToDg = ['RECEPTION_DRAFT', 'SUBMITTED_TO_ASSISTANT'].includes(doc.status);
  const isReceptionOrAssistant = canRegister.value || canAssist.value || (doc.registered_by === userStore.id);

  return notYetSubmittedToDg && isReceptionOrAssistant;
};

// កំណត់ទីតាំងឯកសារ និងអ្នកកាន់បច្ចុប្បន្ន (Current Custodian & Desk)
const getCurrentCustodian = (doc) => {
  if (!doc) return { label: '---', badgeClass: 'badge-light text-muted', icon: 'fas fa-question-circle', subText: '' };

  switch (doc.status) {
    case 'RECEPTION_DRAFT':
      return {
        label: 'តុអ្នកទទួលឯកសារ',
        badgeClass: 'badge-info',
        icon: 'fas fa-inbox',
        subText: doc.registered_by_user ? `(ចុះដោយ៖ ${doc.registered_by_user.name_kh || doc.registered_by_user.name})` : 'រង់ចាំបញ្ជូនទៅជំនួយការ'
      };

    case 'SUBMITTED_TO_ASSISTANT':
      return {
        label: 'តុជំនួយការអគ្គនាយក',
        badgeClass: 'badge-indigo text-white',
        icon: 'fas fa-user-shield',
        subText: 'រង់ចាំជំនួយការពិនិត្យ & ឯ.ឧ. ចាត់ចែង'
      };

    case 'SUBMITTED_TO_DG':
      return {
        label: 'តុឯកឧត្តមអគ្គនាយក',
        badgeClass: 'badge-rose text-white',
        icon: 'fas fa-signature',
        subText: 'រង់ចាំចំណារឯកឧត្តមអគ្គនាយក'
      };

    case 'DG_ANNOTATED':
      return {
        label: 'តុជំនួយការអគ្គនាយក',
        badgeClass: 'badge-warning text-dark',
        icon: 'fas fa-share-square',
        subText: 'រង់ចាំ Scan ចំណារ & ចែកចាយ'
      };

    case 'DISPATCHED':
      if (doc.assignments && doc.assignments.length > 0) {
        const names = doc.assignments.map(a => {
          if (a.target_type === 'USER') {
            return a.user?.name_kh || a.user?.name || a.target_label_kh;
          } else if (a.target_type === 'WORKING_GROUP') {
            return (a.working_group || a.workingGroup)?.name_kh || a.target_label_kh;
          } else if (a.target_type === 'OFFICE') {
            return (a.office)?.name_kh || a.target_label_kh;
          } else if (a.target_type === 'DEPARTMENT') {
            return (a.department)?.name_kh || a.target_label_kh;
          }
          return a.target_label_kh || null;
        }).filter(Boolean);

        if (names.length > 0) {
          const uniqueNames = [...new Set(names)];
          return {
            label: uniqueNames.join('、 '),
            badgeClass: 'badge-primary',
            icon: 'fas fa-users',
            subText: 'ភាគីទទួលបន្ទុកចាត់ចែង'
          };
        }
      }

      if (doc.target_user) {
        return {
          label: doc.target_user.name_kh || doc.target_user.name,
          badgeClass: 'badge-primary',
          icon: 'fas fa-user',
          subText: doc.target_office?.name_kh || doc.target_department?.name_kh || 'មន្ត្រីទទួលបន្ទុក'
        };
      }
      if (doc.target_office) {
        return {
          label: doc.target_office.name_kh,
          badgeClass: 'badge-primary',
          icon: 'fas fa-door-closed',
          subText: doc.target_department?.name_kh || 'ការិយាល័យ'
        };
      }
      if (doc.target_department) {
        return {
          label: doc.target_department.name_kh,
          badgeClass: 'badge-primary',
          icon: 'fas fa-building',
          subText: 'នាយកដ្ឋាន'
        };
      }
      return {
        label: 'បានចែកចាយ',
        badgeClass: 'badge-secondary',
        icon: 'fas fa-check',
        subText: 'រង់ចាំការចាត់ចែងបន្ត'
      };

    case 'IN_RESPONSE_PROGRESS':
      const latestResp = doc.latest_response;
      const approverName = latestResp?.current_approver ? (latestResp.current_approver.name_kh || latestResp.current_approver.name) : 'ថ្នាក់ដឹកនាំ';
      const drafterName = latestResp?.drafted_by_user ? (latestResp.drafted_by_user.name_kh || latestResp.drafted_by_user.name) : '';
      return {
        label: `កំពុងឆ្លង៖ ${approverName}`,
        badgeClass: 'badge-warning text-dark',
        icon: 'fas fa-hourglass-half',
        subText: drafterName ? `រៀបចំដោយ៖ ${drafterName}` : 'រង់ចាំការពិនិត្យ'
      };

    case 'COMPLETED':
      return {
        label: 'បានបញ្ចប់ស្ថាពរ',
        badgeClass: 'badge-success',
        icon: 'fas fa-check-circle',
        subText: 'បណ្ណសារ / រក្សាទុក'
      };

    case 'CANCELLED':
      return {
        label: 'បានលុបចោល',
        badgeClass: 'badge-danger',
        icon: 'fas fa-times-circle',
        subText: 'ឯកសារលុបចោល'
      };

    default:
      return {
        label: doc.status_kh || doc.status,
        badgeClass: 'badge-secondary',
        icon: 'fas fa-file',
        subText: ''
      };
  }
};

const getFullImageUrl = (path) => {
  if (!path) return '';

  if (
    path.startsWith('http://') ||
    path.startsWith('https://') ||
    path.startsWith('blob:') ||
    path.startsWith('data:')
  ) {
    return path;
  }

  const backendBase = (import.meta.env.VITE_APP_API_URL || import.meta.env.VITE_API_URL || 'http://localhost:8000')
    .replace(/\/api\/?$/, '');

  let cleanPath = path.replace(/^\//, '');

  if (cleanPath.includes('users/profile-images/') && !cleanPath.includes('thumbnails/')) {
    cleanPath = cleanPath.replace('users/profile-images/', 'users/profile-images/thumbnails/');
  }

  if (!cleanPath.startsWith('storage/') && !cleanPath.startsWith('uploads/')) {
    cleanPath = `storage/${cleanPath}`;
  }

  return `${backendBase}/${cleanPath}`;
};

const getDepartmentDirector = (deptId) => {
  if (!deptId || !recipientOptions.value?.users) return null;
  const deptUsers = recipientOptions.value.users.filter(u => Number(u.department_id) === Number(deptId));
  if (deptUsers.length === 0) return null;
  return [...deptUsers].sort((a, b) => (Number(a.position?.level || 999) - Number(b.position?.level || 999)))[0];
};

const getOfficeChief = (officeId) => {
  if (!officeId || !recipientOptions.value?.users) return null;
  const offUsers = recipientOptions.value.users.filter(u => Number(u.office_id) === Number(officeId));
  if (offUsers.length === 0) return null;
  return [...offUsers].sort((a, b) => (Number(a.position?.level || 999) - Number(b.position?.level || 999)))[0];
};

const getWorkingGroupLeader = (wgId, wgObj) => {
  if (wgObj?.members) {
    const leader = wgObj.members.find(m => m.role === 'LEADER' && m.status === 'ACTIVE');
    if (leader?.user) return leader.user;
  }
  if (!wgId || !recipientOptions.value?.working_groups) return null;
  const wg = recipientOptions.value.working_groups.find(g => Number(g.id) === Number(wgId));
  const leader = wg?.members?.find(m => m.role === 'LEADER' && m.status === 'ACTIVE');
  return leader?.user || null;
};

// បង្ហាញតែរូបភាព Profile អ្នកដែលឯកសារកំពុងនៅក្នុងដៃអ្នកនោះ (ករណីនាយកដ្ឋាន ការិយាល័យ និងក្រុមការងារ បង្ហាញរូបប្រធាន)
const getCurrentCustodianProfiles = (doc) => {
  if (!doc) return [];

  // 1. ករណីមានឯកសារចេញ (Outbound Response)៖ កន្លែងស្ថានភាពឯកសារចូលគឺត្រូវមានរូបដូចគ្នា (អ្នកកាន់ឯកសារក្នុងដៃ)
  const resp = doc.latest_response || doc.latestResponse;
  if (resp && doc.status !== 'COMPLETED') {
    if (resp.status === 'UNDER_REVIEW') {
      const approver = resp.current_approver || resp.currentApprover;
      if (approver) {
        const name = approver.name_kh || approver.name || 'ថ្នាក់ដឹកនាំ';
        const avatar = approver.profile_image || '';
        return [{
          name,
          title: `កំពុងស្ថិតលើដៃ៖ ${name} (${approver.position?.title_kh || 'ថ្នាក់ដឹកនាំកំពុងពិនិត្យរបាយការណ៍'})`,
          role: approver.position?.title_kh || 'ថ្នាក់ដឹកនាំ',
          avatarUrl: avatar ? getFullImageUrl(avatar) : '',
          initial: name.charAt(0) || 'ថ',
        }];
      }
    } else if (resp.status === 'APPROVED_BY_DG') {
      const dg = resp.approved_by_user || resp.approvedByUser || resp.current_approver || resp.currentApprover;
      if (dg) {
        const name = dg.name_kh || dg.name || 'ឯ.ឧ អគ្គនាយក';
        const avatar = dg.profile_image || '';
        return [{
          name,
          title: `បានឯកភាពដោយ៖ ${name} (ឯ.ឧ អគ្គនាយក)`,
          role: 'ឯ.ឧ អគ្គនាយក',
          avatarUrl: avatar ? getFullImageUrl(avatar) : '',
          initial: name.charAt(0) || 'អ',
        }];
      }
    } else {
      // DRAFT or RETURNED
      const drafter = resp.drafted_by_user || resp.draftedByUser;
      if (drafter) {
        const name = drafter.name_kh || drafter.name || 'មន្ត្រីរៀបចំ';
        const avatar = drafter.profile_image || '';
        const isReturned = resp.status === 'RETURNED';
        return [{
          name,
          title: isReturned
            ? `បានបញ្ជូនត្រឡប់មក៖ ${name} (${drafter.position?.title_kh || 'មន្ត្រីរៀបចំ'})`
            : `កំពុងស្ថិតលើដៃ៖ ${name} (${drafter.position?.title_kh || 'កំពុងរៀបចំរបាយការណ៍'})`,
          role: 'មន្ត្រីរៀបចំ',
          avatarUrl: avatar ? getFullImageUrl(avatar) : '',
          initial: name.charAt(0) || 'ម',
        }];
      }
    }
  }

  // 2. RECEPTION_DRAFT
  if (doc.status === 'RECEPTION_DRAFT') {
    const u = doc.registered_by_user || doc.registeredByUser;
    const name = u?.name_kh || u?.name || 'មន្ត្រីចុះបញ្ជី';
    const avatar = u?.profile_image || '';
    return [{
      name,
      title: `${name} (ច្រកចូល / មន្ត្រីចុះបញ្ជី)`,
      role: 'មន្ត្រីចុះបញ្ជី',
      avatarUrl: avatar ? getFullImageUrl(avatar) : '',
      initial: name.charAt(0) || 'ម',
    }];
  }

  // 3. SUBMITTED_TO_ASSISTANT
  if (doc.status === 'SUBMITTED_TO_ASSISTANT') {
    const u = doc.assistant || recipientOptions.value?.users?.find(usr => usr.permissions?.includes('inbound-documents-assistant'));
    const name = u?.name_kh || u?.name || 'ជំនួយការអគ្គនាយក';
    const avatar = u?.profile_image || '';
    return [{
      name,
      title: `${name} (ជំនួយការអគ្គនាយក)`,
      role: 'ជំនួយការ',
      avatarUrl: avatar ? getFullImageUrl(avatar) : '',
      initial: name.charAt(0) || 'ជ',
    }];
  }

  // 4. SUBMITTED_TO_DG
  if (doc.status === 'SUBMITTED_TO_DG') {
    const dg = recipientOptions.value?.users?.find(usr => Number(usr.position?.level) === 1);
    const name = dg?.name_kh || dg?.name || 'ឯកឧត្តមអគ្គនាយក';
    const avatar = dg?.profile_image || '';
    return [{
      name,
      title: `${name} (ឯកឧត្តមអគ្គនាយក)`,
      role: 'អគ្គនាយក',
      avatarUrl: avatar ? getFullImageUrl(avatar) : '',
      initial: name.charAt(0) || 'អ',
    }];
  }

  // 5. DG_ANNOTATED
  if (doc.status === 'DG_ANNOTATED') {
    const u = doc.assistant || recipientOptions.value?.users?.find(usr => usr.permissions?.includes('inbound-documents-assistant'));
    const name = u?.name_kh || u?.name || 'ជំនួយការអគ្គនាយក';
    const avatar = u?.profile_image || '';
    return [{
      name,
      title: `${name} (ជំនួយការអគ្គនាយក - រង់ចាំចែកចាយ)`,
      role: 'ជំនួយការ',
      avatarUrl: avatar ? getFullImageUrl(avatar) : '',
      initial: name.charAt(0) || 'ជ',
    }];
  }

  // 6. COMPLETED
  if (doc.status === 'COMPLETED') {
    const u = doc.acknowledged_by_user || doc.acknowledgedByUser;
    const name = u?.name_kh || u?.name || 'បានបញ្ចប់';
    const avatar = u?.profile_image || '';
    return [{
      name,
      title: `${name} (បានបញ្ចប់ដំណើរការ)`,
      role: 'បានបញ្ចប់',
      avatarUrl: avatar ? getFullImageUrl(avatar) : '',
      initial: name.charAt(0) || 'ប',
    }];
  }

  // 7. DISPATCHED (or other active status)
  const assignments = doc.assignments || [];
  const pendingAssignments = assignments.filter(a => a.status === 'PENDING');
  const targetAssignments = pendingAssignments.length > 0 ? pendingAssignments : assignments.filter(a => a.status !== 'RECALLED');

  if (targetAssignments.length > 0) {
    const profiles = [];
    const seenNames = new Set();

    targetAssignments.forEach(a => {
      // Case A: Officer
      if (a.target_type === 'USER' || a.user || a.user_id) {
        let u = a.user;
        if (!u && a.user_id && recipientOptions.value?.users) {
          u = recipientOptions.value.users.find(usr => Number(usr.id) === Number(a.user_id));
        }
        const name = u?.name_kh || u?.name || a.target_label_kh || 'មន្ត្រីទទួលបន្ទុក';
        if (seenNames.has(name)) return;
        seenNames.add(name);
        const avatar = u?.profile_image || '';
        profiles.push({
          name,
          title: `${name} (${u?.position?.title_kh || 'មន្ត្រីទទួលបន្ទុក'})`,
          role: u?.position?.title_kh || 'មន្ត្រីទទួលបន្ទុក',
          avatarUrl: avatar ? getFullImageUrl(avatar) : '',
          initial: name.charAt(0) || 'ម',
        });
      }
      // Case B: Office -> Show Office Chief profile!
      else if (a.target_type === 'OFFICE' || a.office_id) {
        const chief = getOfficeChief(a.office_id || a.office?.id);
        const officeName = a.office?.name_kh || 'ការិយាល័យ';
        const name = chief ? (chief.name_kh || chief.name) : `ប្រធាន${officeName}`;
        if (seenNames.has(name)) return;
        seenNames.add(name);
        const avatar = chief?.profile_image || '';
        profiles.push({
          name,
          title: `${name} (ប្រធាន${officeName})`,
          role: `ប្រធាន${officeName}`,
          avatarUrl: avatar ? getFullImageUrl(avatar) : '',
          initial: name.charAt(0) || 'ប',
        });
      }
      // Case C: Department -> Show Department Director profile!
      else if (a.target_type === 'DEPARTMENT' || a.department_id) {
        const director = getDepartmentDirector(a.department_id || a.department?.id);
        const deptName = a.department?.name_kh || 'នាយកដ្ឋាន';
        const name = director ? (director.name_kh || director.name) : `ប្រធាន${deptName}`;
        if (seenNames.has(name)) return;
        seenNames.add(name);
        const avatar = director?.profile_image || '';
        profiles.push({
          name,
          title: `${name} (ប្រធាន${deptName})`,
          role: `ប្រធាន${deptName}`,
          avatarUrl: avatar ? getFullImageUrl(avatar) : '',
          initial: name.charAt(0) || 'ប',
        });
      }
      // Case D: Working Group -> Show Working Group Leader profile!
      else if (a.target_type === 'WORKING_GROUP' || a.working_group_id) {
        const leader = getWorkingGroupLeader(a.working_group_id || a.workingGroup?.id, a.workingGroup);
        const wgName = (a.workingGroup || a.working_group)?.name_kh || 'ក្រុមការងារ';
        const name = leader ? (leader.name_kh || leader.name) : `ប្រធាន${wgName}`;
        if (seenNames.has(name)) return;
        seenNames.add(name);
        const avatar = leader?.profile_image || '';
        profiles.push({
          name,
          title: `${name} (ប្រធាន${wgName})`,
          role: `ប្រធាន${wgName}`,
          avatarUrl: avatar ? getFullImageUrl(avatar) : '',
          initial: name.charAt(0) || 'ប',
        });
      }
    });

    if (profiles.length > 0) return [profiles[0]];
  }

  // Direct targets on doc
  if (doc.target_user) {
    const name = doc.target_user.name_kh || doc.target_user.name;
    const avatar = doc.target_user.profile_image || '';
    return [{
      name,
      title: `${name} (${doc.target_user.position?.title_kh || 'មន្ត្រីទទួលបន្ទុក'})`,
      role: doc.target_user.position?.title_kh || 'មន្ត្រីទទួលបន្ទុក',
      avatarUrl: avatar ? getFullImageUrl(avatar) : '',
      initial: name.charAt(0) || 'ម',
    }];
  }

  if (doc.target_office) {
    const chief = getOfficeChief(doc.target_office_id || doc.target_office.id);
    const officeName = doc.target_office.name_kh;
    const name = chief ? (chief.name_kh || chief.name) : `ប្រធាន${officeName}`;
    const avatar = chief?.profile_image || '';
    return [{
      name,
      title: `${name} (ប្រធាន${officeName})`,
      role: `ប្រធាន${officeName}`,
      avatarUrl: avatar ? getFullImageUrl(avatar) : '',
      initial: name.charAt(0) || 'ប',
    }];
  }

  if (doc.target_department) {
    const director = getDepartmentDirector(doc.target_department_id || doc.target_department.id);
    const deptName = doc.target_department.name_kh;
    const name = director ? (director.name_kh || director.name) : `ប្រធាន${deptName}`;
    const avatar = director?.profile_image || '';
    return [{
      name,
      title: `${name} (ប្រធាន${deptName})`,
      role: `ប្រធាន${deptName}`,
      avatarUrl: avatar ? getFullImageUrl(avatar) : '',
      initial: name.charAt(0) || 'ប',
    }];
  }

  return [];
};

// កំណត់ស្ថានភាពសាមញ្ញ ៣ ដំណាក់កាល (Pending, Doing, Complete)
const getSimplifiedStatus = (doc) => {
  if (!doc) return { label: 'Pending', khLabel: 'ចូលដំបូង / រង់ចាំ', key: 'PENDING', badgeClass: 'badge-warning text-dark font-weight-bold', icon: 'fas fa-clock', subText: '' };

  const status = doc.status;

  // ១. ករណីបញ្ចប់ (Complete)
  if (status === 'COMPLETED') {
    return {
      label: 'Complete',
      khLabel: 'បានបញ្ចប់សព្វគ្រប់',
      key: 'COMPLETE',
      badgeClass: 'badge-success font-weight-bold',
      icon: 'fas fa-check-circle',
      subText: doc.acknowledged_at ? 'បានទទួលជ្រាប និងបញ្ចប់' : 'អគ្គនាយកបានឯកភាព'
    };
  }

  // ២. ករណីកំពុងដំណើរការ (Doing) - ឯកសារបានចាត់ចែងចេញ, កំពុងរៀបចំឆ្លើយតប, ឬមានចំណារអគ្គនាយក
  if (['DISPATCHED', 'IN_RESPONSE_PROGRESS', 'DG_ANNOTATED'].includes(status) || doc.dispatched_at) {
    const userId = Number(userStore.id);
    const hasUserForwarded = doc.assignments?.some(a => Number(a.assigned_by || a.assigned_by_user_id) === userId);
    const hasUserDrafted = doc.responses?.some(r => Number(r.drafted_by) === userId);

    let sub = 'កំពុងដំណើរការ';
    if (hasUserDrafted) {
      sub = 'បានរៀបចំសេចក្តីព្រាង';
    } else if (hasUserForwarded) {
      sub = 'បានចាត់ចែងបន្ត';
    } else if (status === 'IN_RESPONSE_PROGRESS') {
      sub = 'កំពុងរៀបចំ';
    } else if (status === 'DG_ANNOTATED' && !doc.dispatched_at) {
      sub = 'មានចំណារអគ្គនាយក';
    } else {
      sub = 'កំពុងដំណើរការ';
    }

    return {
      label: 'Doing',
      khLabel: 'កំពុងដំណើរការ',
      key: 'DOING',
      badgeClass: 'badge-primary font-weight-bold',
      icon: 'fas fa-tasks',
      subText: sub
    };
  }

  // ៣. ករណីចូលដំបូង / រង់ចាំចំណារពីថ្នាក់ដឹកនាំ (Pending)
  let sub = 'រង់ចាំ';
  let kh = 'រង់ចាំ';
  if (status === 'RECEPTION_DRAFT') {
    sub = 'នៅតុចុះបញ្ជីទទួល';
    kh = 'នៅតុចុះបញ្ជី';
  } else if (status === 'SUBMITTED_TO_ASSISTANT') {
    sub = 'នៅតុជំនួយការ';
    kh = 'រង់ចាំជំនួយការពិនិត្យ';
  } else if (status === 'SUBMITTED_TO_DG') {
    sub = 'នៅតុឯកឧត្តមអគ្គនាយក';
    kh = 'រង់ចាំចំណារអគ្គនាយក';
  }

  return {
    label: 'Pending',
    khLabel: kh,
    key: 'PENDING',
    badgeClass: 'badge-warning text-dark font-weight-bold',
    icon: 'fas fa-clock',
    subText: sub
  };
};

// --- API Calls & Data Loading ---
const loadDocuments = async () => {
  loading.value = true;
  try {
    const params = {
      scope: activeTab.value,
      page: filters.page,
      search: filters.search,
      status: filters.status,
      dg_inbound_category: filters.dg_inbound_category,
      urgency: filters.urgency,
      confidentiality: filters.confidentiality,
      deadline_filter: filters.deadline_filter,
      from_date: filters.from_date,
      to_date: filters.to_date,
    };
    const res = await apiGetInboundDocuments(params);
    documents.value = res.data;
  } catch (err) {
    console.error('Failed to load inbound documents:', err);
  } finally {
    loading.value = false;
  }
};

const loadStats = async () => {
  try {
    const res = await apiGetInboundDocumentStats();
    stats.value = res.data;
  } catch (err) {
    console.error('Failed to load stats:', err);
  }
};

const loadRecipientsOptions = async () => {
  try {
    const res = await apiGetInboundRecipientsOptions();
    recipientOptions.value = res.data;
  } catch (err) {
    console.error('Failed to load recipients options:', err);
  }
};

const syncTabFromRoute = () => {
  const t = route.query.tab;
  if (['reception', 'assistant_inbox', 'dg_inbox'].includes(t)) {
    activeTab.value = 'pending';
  } else if (t && ['all', 'pending', 'doing', 'completed', 'my_unit'].includes(t)) {
    activeTab.value = t;
  } else {
    activeTab.value = 'all';
  }
};

const switchTab = (tab) => {
  activeTab.value = tab;
  filters.status = '';
  filters.deadline_filter = '';
  filters.page = 1;
  const defaultTab = 'all';
  const currentTab = route.query.tab || defaultTab;
  if (currentTab !== tab) {
    router.replace({
      name: 'inbound-documents',
      query: tab === defaultTab ? {} : { ...route.query, tab }
    });
  }
  loadDocuments();
};

watch(
  () => route.query.tab,
  () => {
    syncTabFromRoute();
    filters.status = '';
    filters.deadline_filter = '';
    filters.page = 1;
    loadDocuments();
  }
);

const toggleDeadlineFilter = (type) => {
  if (filters.deadline_filter === type) {
    filters.deadline_filter = '';
  } else {
    filters.deadline_filter = type;
    filters.status = '';
    activeTab.value = 'all';
  }
  filters.page = 1;
  loadDocuments();
};

const toggleStatusFilter = (statusVal) => {
  if (filters.status === statusVal) {
    filters.status = '';
  } else {
    filters.status = statusVal;
    filters.deadline_filter = '';
    activeTab.value = 'all';
  }
  filters.page = 1;
  loadDocuments();
};

const changePage = (page) => {
  if (page < 1 || page > documents.value.last_page) return;
  filters.page = page;
  loadDocuments();
};

const resetFilters = () => {
  filters.search = '';
  filters.status = '';
  filters.dg_inbound_category = '';
  filters.urgency = '';
  filters.confidentiality = '';
  filters.deadline_filter = '';
  filters.from_date = '';
  filters.to_date = '';
  filters.page = 1;
  loadDocuments();
};

// --- Modal Handlers ---
const openCreateModal = async () => {
  await fetchNextGeneralNumber();
  createForm.received_date = new Date().toISOString().substring(0, 10);
  createForm.received_time = new Date().toTimeString().substring(0, 5);
  createForm.deliverer_name = '';
  createForm.deliverer_phone = '';
  createForm.sender_organization = '';
  senderOrgSelection.value = '';
  customSenderOrg.value = '';
  orgSearchQuery.value = '';
  isOrgDropdownOpen.value = false;
  createForm.external_reference_number = '';
  createForm.external_document_date = '';
  createForm.deadline = '';
  createForm.title = '';
  createForm.document_type = 'LETTER';
  createForm.urgency = 'NORMAL';
  createForm.confidentiality = 'NORMAL';
  createForm.receptionist_notes = '';
  createForm.send_immediately = true;
  selectedOriginalFile.value = null;
  selectedOriginalFileName.value = '';
  if (senderOrganizations.value.length === 0) {
    loadSenderOrganizations();
  }
  showCreateModal.value = true;
};

const closeCreateModal = () => {
  showCreateModal.value = false;
};

const openEditModal = (doc) => {
  if (!doc) return;
  selectedEditDoc.value = doc;
  editForm.general_inbound_number = doc.general_inbound_number || '';
  editForm.received_date = doc.received_date ? doc.received_date.substring(0, 10) : '';
  editForm.received_time = doc.received_time ? doc.received_time.substring(0, 5) : '';
  editForm.deliverer_name = doc.deliverer_name || '';
  editForm.deliverer_phone = doc.deliverer_phone || '';
  editForm.sender_organization = doc.sender_organization || '';
  editForm.external_reference_number = doc.external_reference_number || '';
  editForm.external_document_date = doc.external_document_date ? doc.external_document_date.substring(0, 10) : '';
  editForm.deadline = doc.deadline ? doc.deadline.substring(0, 10) : '';
  editForm.title = doc.title || '';
  editForm.document_type = doc.document_type || 'LETTER';
  editForm.urgency = doc.urgency || 'NORMAL';
  editForm.confidentiality = doc.confidentiality || 'NORMAL';
  editForm.receptionist_notes = doc.receptionist_notes || '';
  editForm.original_file = null;
  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
  selectedEditDoc.value = null;
};

const onEditFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    editForm.original_file = file;
  }
};

const submitEditDocument = async () => {
  if (!selectedEditDoc.value) return;
  editingDoc.value = true;
  try {
    const fd = new FormData();
    fd.append('general_inbound_number', editForm.general_inbound_number);
    fd.append('received_date', editForm.received_date);
    fd.append('received_time', editForm.received_time);
    fd.append('deliverer_name', editForm.deliverer_name);
    fd.append('deliverer_phone', editForm.deliverer_phone || '');
    fd.append('sender_organization', editForm.sender_organization);
    fd.append('external_reference_number', editForm.external_reference_number || '');
    fd.append('external_document_date', editForm.external_document_date || '');
    fd.append('deadline', editForm.deadline || '');
    fd.append('title', editForm.title);
    fd.append('document_type', editForm.document_type);
    fd.append('urgency', editForm.urgency);
    fd.append('confidentiality', editForm.confidentiality);
    fd.append('receptionist_notes', editForm.receptionist_notes || '');
    if (editForm.original_file) {
      fd.append('original_file', editForm.original_file);
    }

    await apiUpdateInboundDocument(selectedEditDoc.value.id, fd);
    Swal.fire({
      icon: 'success',
      title: 'បានកែប្រែជោគជ័យ!',
      text: 'ព័ត៌មានលម្អិតឯកសារចូលត្រូវបានកែសម្រួលរួចរាល់។',
      timer: 1500,
      showConfirmButton: false,
    });
    showEditModal.value = false;
    if (showDetailModal.value && selectedDoc.value) {
      openDetailModal(selectedEditDoc.value);
    }
    loadDocuments();
    loadStats();
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យក្នុងការកែប្រែ',
      text: err.response?.data?.message || 'សូមពិនិត្យមើលទិន្នន័យដែលបានបញ្ចូលឡើងវិញ!',
    });
  } finally {
    editingDoc.value = false;
  }
};

const fetchNextGeneralNumber = async () => {
  try {
    const res = await apiGenerateInboundNumber({ type: 'general' });
    createForm.general_inbound_number = res.data.formatted_number;
  } catch (err) {
    console.error('Failed to generate general number:', err);
  }
};

const fetchNextDgNumber = async () => {
  try {
    const res = await apiGenerateInboundNumber({
      type: 'dg',
      category: assistantReceiveForm.dg_inbound_category
    });
    assistantReceiveForm.custom_dg_number = res.data.formatted_number;
  } catch (err) {
    console.error('Failed to generate DG number:', err);
  }
};

const handleOriginalFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedOriginalFile.value = file;
    selectedOriginalFileName.value = file.name;
  }
};

const handleAnnotatedFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedAnnotatedFile.value = file;
    selectedAnnotatedFileName.value = file.name;
  }
};

const handleResponseFileChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    selectedResponseFile.value = file;
    selectedResponseFileName.value = file.name;
  }
};

const onDispatchDeptChange = () => {
  dispatchForm.target_office_id = '';
  dispatchForm.target_user_id = '';
};

// --- Submit Handlers ---
const submitCreateDocument = async () => {
  if (senderOrgSelection.value === '__OTHER__') {
    createForm.sender_organization = customSenderOrg.value.trim();
  } else {
    createForm.sender_organization = senderOrgSelection.value.trim();
  }

  if (!createForm.sender_organization) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមបញ្ជាក់អង្គភាព/ស្ថាប័ន!',
      text: 'សូមជ្រើសរើសស្ថាប័ន ឬជ្រើស "ផ្សេងៗ" រួចវាយបញ្ចូលឈ្មោះអង្គភាព/ស្ថាប័ន',
      confirmButtonText: 'យល់ព្រម',
    });
    return;
  }

  if (!createForm.general_inbound_number || !createForm.general_inbound_number.trim()) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមបញ្ជាក់លេខចូលទូទៅ!',
      text: 'សូមវាយបញ្ចូលលេខចូលទូទៅ ឬចុចប៊ូតុងបង្កើតលេខស្វ័យប្រវត្តិ',
      confirmButtonText: 'យល់ព្រម',
    });
    return;
  }

  submitting.value = true;
  try {
    const formData = new FormData();
    Object.keys(createForm).forEach(k => {
      formData.append(k, createForm[k] ?? '');
    });
    if (selectedOriginalFile.value) {
      formData.append('original_file', selectedOriginalFile.value);
    }
    await apiCreateInboundDocument(formData);
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានចុះបញ្ជីឯកសារចូលថ្មីដោយជោគជ័យ!',
      confirmButtonText: 'យល់ព្រម',
    });
    closeCreateModal();
    loadDocuments();
    loadStats();
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ!',
      text: err.response?.data?.message || 'មានបញ្ហាក្នុងការចុះបញ្ជីឯកសារ!',
      confirmButtonText: 'យល់ព្រម',
    });
  } finally {
    submitting.value = false;
  }
};

const sendToAssistantAction = async (doc) => {
  const confirm = await Swal.fire({
    title: 'បញ្ជូនទៅជំនួយការអគ្គនាយក?',
    text: `តើលោកអ្នកពិតជាចង់បញ្ជូនឯកសារលេខ ${doc.general_inbound_number} ទៅកាន់ជំនួយការអគ្គនាយកមែនទេ?`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'បញ្ជូនភ្លាម',
    cancelButtonText: 'បោះបង់',
  });
  if (!confirm.isConfirmed) return;

  try {
    await apiSendToAssistant(doc.id);
    Swal.fire({ icon: 'success', title: 'បានបញ្ជូន!', timer: 1500, showConfirmButton: false });
    loadDocuments();
    loadStats();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចបញ្ជូនបានឡើយ!' });
  }
};

const openAssistantReceiveModal = async (doc) => {
  selectedDoc.value = doc;
  assistantReceiveForm.dg_inbound_category = 'COMPANY';
  assistantReceiveForm.dg_received_date = new Date().toISOString().substring(0, 10);
  assistantReceiveForm.dg_assistant_notes = '';
  await fetchNextDgNumber();
  showAssistantReceiveModal.value = true;
};

const submitAssistantReceive = async () => {
  submitting.value = true;
  try {
    await apiAssistantReceiveAndSubmitToDg(selectedDoc.value.id, assistantReceiveForm);
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: `បានចុះលេខចូល ${assistantReceiveForm.custom_dg_number} និងដាក់ជូនឯកឧត្តមអគ្គនាយករួចរាល់!`,
      confirmButtonText: 'យល់ព្រម',
    });
    showAssistantReceiveModal.value = false;
    loadDocuments();
    loadStats();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចចុះលេខបានឡើយ!' });
  } finally {
    submitting.value = false;
  }
};

const syncDgTargetsToAnnotation = () => {
  const names = [];
  (dgAnnotateForm.department_ids || []).forEach(id => {
    const d = (recipientOptions.departments || []).find(item => item.id === id);
    if (d) names.push(d.name_kh || d.name);
  });
  (dgAnnotateForm.office_ids || []).forEach(id => {
    const o = (recipientOptions.offices || []).find(item => item.id === id);
    if (o) names.push(o.name_kh || o.name);
  });
  (dgAnnotateForm.user_ids || []).forEach(id => {
    const u = (recipientOptions.officers || []).find(item => item.id === id);
    if (u) names.push(u.name_kh || u.name);
  });
  (dgAnnotateForm.working_group_ids || []).forEach(id => {
    const wg = (recipientOptions.working_groups || []).find(item => item.id === id);
    if (wg) names.push(wg.name_kh || wg.name);
  });

  if (names.length > 0) {
    const uniqueNames = [...new Set(names)];
    dgAnnotateForm.dg_annotation = `ជូន ${uniqueNames.join('、 ')} ចាត់ចែង`;
  } else {
    dgAnnotateForm.dg_annotation = '';
  }
};

const openDgAnnotateModal = (doc) => {
  selectedDoc.value = doc;
  selectedDgAnnotatedFile.value = null;
  selectedDgAnnotatedFileName.value = '';
  dgAnnotateForm.dg_annotation = doc.dg_annotation || '';
  dgAnnotateForm.is_response_required = !!doc.is_response_required;
  dgAnnotateForm.deadline = doc.deadline ? String(doc.deadline).substring(0, 10) : '';
  dgAnnotateForm.department_ids = [];
  dgAnnotateForm.office_ids = [];
  dgAnnotateForm.user_ids = [];
  dgAnnotateForm.working_group_ids = [];
  dgAnnotateForm.dispatch_notes = '';
  officerDgSearch.value = '';
  showDgAnnotateModal.value = true;
};

const submitDgAnnotate = async () => {
  if (!dgAnnotateForm.dg_annotation?.trim()) {
    Swal.fire({ icon: 'warning', title: 'សូមបំពេញ', text: 'សូមបញ្ចូលខ្លឹមសារចំណាររបស់ឯកឧត្តមអគ្គនាយក!' });
    return;
  }
  submitting.value = true;
  try {
    const formData = new FormData();
    formData.append('dg_annotation', dgAnnotateForm.dg_annotation);
    formData.append('is_response_required', dgAnnotateForm.is_response_required ? '1' : '0');
    if (dgAnnotateForm.deadline) formData.append('deadline', dgAnnotateForm.deadline);
    if (dgAnnotateForm.dispatch_notes) formData.append('dispatch_notes', dgAnnotateForm.dispatch_notes);

    dgAnnotateForm.department_ids.forEach(id => formData.append('department_ids[]', id));
    dgAnnotateForm.office_ids.forEach(id => formData.append('office_ids[]', id));
    dgAnnotateForm.user_ids.forEach(id => formData.append('user_ids[]', id));
    dgAnnotateForm.working_group_ids.forEach(id => formData.append('working_group_ids[]', id));

    if (selectedDgAnnotatedFile.value) {
      formData.append('annotated_file', selectedDgAnnotatedFile.value);
    }

    const res = await apiDgAnnotate(selectedDoc.value.id, formData);
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: res.data?.message || 'បានកត់ត្រាចំណារ និងចាត់ចែងរួចរាល់!',
      confirmButtonText: 'យល់ព្រម',
    });
    showDgAnnotateModal.value = false;
    loadDocuments();
    loadStats();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចកត់ត្រាចំណារបានឡើយ!' });
  } finally {
    submitting.value = false;
  }
};

const openForwardModal = (doc) => {
  selectedDoc.value = doc;
  const isSuper = isAdmin.value || (userStore.position && Number(userStore.position.level) <= 2) || canAssist.value;
  const isDeptDirector = userStore.position && Number(userStore.position.level) <= 4;
  const isDeptLeader = userStore.position && Number(userStore.position.level) <= 5;
  const isOfficeLeader = userStore.position && Number(userStore.position.level) <= 8;
  const isWgLeader = isUserLeaderOfAssignedWorkingGroup(doc);

  if (isDeptLeader) {
    forwardForm.target_type = 'OFFICE';
  } else if (isOfficeLeader) {
    forwardForm.target_type = 'OFFICER';
  } else if (isWgLeader) {
    forwardForm.target_type = 'WG_MEMBER';
  } else {
    forwardForm.target_type = 'OFFICER';
  }

  forwardForm.target_department_id = doc.target_department_id || '';
  forwardForm.target_office_id = doc.target_office_id || '';
  forwardForm.target_user_id = doc.target_user_id || '';
  forwardForm.target_working_group_id = '';
  forwardForm.user_ids = [];
  forwardForm.forwarding_notes = '';
  officerForwardSearch.value = '';
  showForwardModal.value = true;
};

const handleForwardSubmit = async () => {
  if (!selectedDoc.value) return;
  if (forwardForm.target_type === 'DEPARTMENT' && !forwardForm.target_department_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសនាយកដ្ឋានគោលដៅ!' });
    return;
  }
  if (forwardForm.target_type === 'OFFICE' && !forwardForm.target_office_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសការិយាល័យគោលដៅ!' });
    return;
  }
  if ((forwardForm.target_type === 'OFFICER' || forwardForm.target_type === 'WG_MEMBER') && !forwardForm.target_user_id && forwardForm.user_ids.length === 0) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសមន្ត្រីទទួលបន្ទុក!' });
    return;
  }
  if (forwardForm.target_type === 'WORKING_GROUP' && !forwardForm.target_working_group_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសក្រុមការងារគោលដៅ!' });
    return;
  }

  forwarding.value = true;
  try {
    const payload = {
      target_type: forwardForm.target_type,
      target_department_id: forwardForm.target_type === 'DEPARTMENT' ? forwardForm.target_department_id : null,
      target_office_id: forwardForm.target_type === 'OFFICE' ? forwardForm.target_office_id : null,
      target_user_id: (forwardForm.target_type === 'OFFICER' || forwardForm.target_type === 'WG_MEMBER') ? (forwardForm.user_ids[0] || forwardForm.target_user_id) : null,
      user_ids: forwardForm.user_ids,
      target_working_group_id: forwardForm.target_type === 'WORKING_GROUP' ? forwardForm.target_working_group_id : null,
      forwarding_notes: forwardForm.forwarding_notes || '',
    };
    await apiForwardInboundDocument(selectedDoc.value.id, payload);
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានចាត់ចែង និងបញ្ជូនបន្តតាមឋានានុក្រមដោយជោគជ័យ!',
      confirmButtonText: 'យល់ព្រម',
    });
    showForwardModal.value = false;
    loadDocuments();
    loadStats();
  } catch (err) {
    const errorMsg = err.response?.data?.message 
      || Object.values(err.response?.data?.errors || {})[0]?.[0]
      || 'មិនអាចបញ្ជូនបន្តបានឡើយ!';
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ!',
      text: errorMsg,
    });
  } finally {
    forwarding.value = false;
  }
};

const acknowledgeAction = async (doc) => {
  const confirm = await Swal.fire({
    title: 'ទទួលជ្រាបឯកសារ?',
    text: `តើលោកអ្នកបានជ្រាប និងបញ្ចប់ដំណើរការឯកសារ ${doc.general_inbound_number} មែនទេ?`,
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'ទទួលជ្រាប & បញ្ចប់',
    cancelButtonText: 'បោះបង់',
  });
  if (!confirm.isConfirmed) return;

  try {
    await apiAcknowledgeInboundDocument(doc.id);
    Swal.fire({ icon: 'success', title: 'ជោគជ័យ!', text: 'បានទទួលជ្រាប និងបញ្ចប់ឯកសារដោយជោគជ័យ!', timer: 1500 });
    if (showDetailModal.value) showDetailModal.value = false;
    loadDocuments();
    loadStats();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចទទួលជ្រាបបានឡើយ!' });
  }
};

const openSubmitResponseModal = async (doc) => {
  selectedDoc.value = doc;
  const existing = doc.latest_response;
  if (existing && ['DRAFT', 'RETURNED'].includes(existing.status)) {
    responseForm.title = existing.title || `លិខិតឆ្លើយតបលើ៖ ${doc.title}`;
    responseForm.response_type = existing.response_type || 'REPORT';
    responseForm.header_organization = existing.header_organization || '';
    responseForm.reference_note = existing.reference_note || '';
    responseForm.content = existing.content || '';
    responseForm.sign_title = existing.sign_title || '';
    responseForm.sign_name = existing.sign_name || (userStore.name_kh || userStore.name);
    responseForm.forwarded_to_id = existing.current_approver_id || '';
    responseForm.comment = '';
  } else {
    responseForm.title = `របាយការណ៍ស្តីពី៖ ${doc.title}`;
    responseForm.response_type = 'REPORT';
    responseForm.header_organization = userStore.department?.name_kh || '';
    responseForm.reference_note = `យោង៖ ចំណារលើឯកសារលេខ ${doc.general_inbound_number} ចុះថ្ងៃទី ${doc.received_date}`;
    responseForm.content = '';
    responseForm.sign_title = userStore.position?.title_kh || 'មន្ត្រី';
    responseForm.sign_name = userStore.name_kh || userStore.name || '';
    responseForm.forwarded_to_id = '';
    responseForm.comment = '';
  }
  selectedResponseFile.value = null;
  selectedResponseFileName.value = '';

  try {
    const res = await apiGetNextResponseApprovers(doc.id);
    responseCandidates.value = res.data.candidates || [];
    showSubmitResponseModal.value = true;
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: 'មិនអាចទាញយកបញ្ជីថ្នាក់ដឹកនាំឆ្លងបានឡើយ!' });
  }
};

const saveResponseDraftOnly = async () => {
  submitting.value = true;
  try {
    const formData = new FormData();
    formData.append('title', responseForm.title || `សេចក្តីព្រាងឆ្លើយតបលើ ${selectedDoc.value?.title}`);
    formData.append('response_type', responseForm.response_type || 'REPORT');
    formData.append('header_organization', responseForm.header_organization || '');
    formData.append('reference_note', responseForm.reference_note || '');
    formData.append('content', responseForm.content || '');
    formData.append('sign_title', responseForm.sign_title || '');
    formData.append('sign_name', responseForm.sign_name || '');
    formData.append('is_draft', '1');
    if (selectedResponseFile.value) {
      formData.append('response_file', selectedResponseFile.value);
    }

    await apiSubmitResponseDraft(selectedDoc.value.id, formData);
    Swal.fire({
      icon: 'success',
      title: 'បានរក្សាទុក!',
      text: 'បានរក្សាទុកសេចក្តីព្រាងដោយជោគជ័យ!',
      timer: 1600,
      showConfirmButton: false,
    });
    loadDocuments();
    loadStats();
  } catch (err) {
    const errorMsg = err.response?.data?.message || Object.values(err.response?.data?.errors || {})[0]?.[0] || 'មិនអាចរក្សាទុកព្រាងបានឡើយ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: errorMsg });
  } finally {
    submitting.value = false;
  }
};

const submitResponseDraftAction = async () => {
  if (!responseForm.forwarded_to_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសថ្នាក់ដឹកនាំដើម្បីឆ្លងបន្ត!' });
    return;
  }
  submitting.value = true;
  try {
    const formData = new FormData();
    formData.append('title', responseForm.title);
    formData.append('response_type', responseForm.response_type || 'REPORT');
    formData.append('header_organization', responseForm.header_organization || '');
    formData.append('reference_note', responseForm.reference_note || '');
    formData.append('content', responseForm.content || '');
    formData.append('sign_title', responseForm.sign_title || '');
    formData.append('sign_name', responseForm.sign_name || '');
    formData.append('forwarded_to_id', responseForm.forwarded_to_id);
    formData.append('comment', responseForm.comment || '');
    formData.append('is_draft', '0');
    if (selectedResponseFile.value) {
      formData.append('response_file', selectedResponseFile.value);
    }

    await apiSubmitResponseDraft(selectedDoc.value.id, formData);
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានដាក់ស្នើព្រាងលិខិតឆ្លើយតបទៅកាន់ថ្នាក់ដឹកនាំរួចរាល់!',
      confirmButtonText: 'យល់ព្រម',
    });
    showSubmitResponseModal.value = false;
    loadDocuments();
    loadStats();
  } catch (err) {
    const errorMsg = err.response?.data?.message || Object.values(err.response?.data?.errors || {})[0]?.[0] || 'មិនអាចដាក់ស្នើឆ្លើយតបបានឡើយ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: errorMsg });
  } finally {
    submitting.value = false;
  }
};

const openReviewResponseModal = async (doc) => {
  selectedDoc.value = doc;
  selectedResponse.value = doc.latest_response;
  responseReviewStep.value = 'form';
  responseReviewWorkingHtml.value = '';
  if (cleanupResponseReviewDraggable) {
    cleanupResponseReviewDraggable();
    cleanupResponseReviewDraggable = null;
  }
  reviewActionForm.action = isDg.value ? 'DG_APPROVE' : 'FORWARD';
  reviewActionForm.forwarded_to_id = '';
  reviewActionForm.response_number = doc.latest_response?.response_number || '';
  reviewActionForm.comment = isDg.value
    ? 'បានពិនិត្យ និងសម្រេចឯកភាព'
    : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច';
  reviewActionForm.embed_into_document = (doc.latest_response?.response_type !== 'LETTER');
  reviewActionForm.placement = 'SIDE_BY_SIDE_LEFT';
  reviewActionForm.sign_date = getShortKhmerDateString(new Date());

  if (doc.latest_response?.id) {
    try {
      const respRes = await apiGetOutboundDocument(doc.latest_response.id);
      if (respRes.data?.data) {
        selectedResponse.value = respRes.data.data;
      }
    } catch (e) {
      console.warn('Could not fetch full outbound doc:', e);
    }
  }

  try {
    const res = await apiGetNextResponseApprovers(doc.id);
    responseCandidates.value = res.data.candidates || [];
    showReviewResponseModal.value = true;
  } catch (err) {
    console.error('Failed to load candidate approvers:', err);
  }
};

const setupResponseReviewPositionDraggable = () => {
  if (cleanupResponseReviewDraggable) {
    cleanupResponseReviewDraggable();
    cleanupResponseReviewDraggable = null;
  }
  if (!responseReviewPositionPaperRef.value) return;

  cleanupResponseReviewDraggable = setupDraggableOpinionBoxes(
    responseReviewPositionPaperRef.value,
    () => {},
    { currentUserId: userStore.id, isAdmin: isDg.value, canEdit: true }
  );

  // Auto-select the newly added box so handles are visible immediately
  nextTick(() => {
    if (!responseReviewPositionPaperRef.value) return;
    const boxes = responseReviewPositionPaperRef.value.querySelectorAll('.draggable-opinion-box');
    boxes.forEach(b => {
      const bOwnerId = b.getAttribute('data-owner-id');
      if (bOwnerId && Number(bOwnerId) === Number(userStore.id)) {
        b.classList.add('is-selected');
      }
    });
  });
};

const goToResponseReviewPositionStep = () => {
  if (reviewActionForm.action === 'FORWARD' && !reviewActionForm.forwarded_to_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសថ្នាក់ដឹកនាំដើម្បីចារឆ្លងបន្ត!' });
    return;
  }

  const isDgLeader = isDg.value || reviewActionForm.action === 'DG_APPROVE';
  const reviewerName = userStore.name_kh || userStore.name || '';
  const reviewerSig = userStore.signature_image || '';
  const shortDateKh = reviewActionForm.sign_date || getShortKhmerDateString(new Date());
  const comment = reviewActionForm.comment || (isDgLeader ? 'បានពិនិត្យ និងសម្រេចឯកភាព' : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច');

  const leaderBoxHtml = generateDraggableOpinionBoxHtml({
    comment,
    reviewerName,
    signatureUrl: reviewerSig,
    shortDateKh,
    ownerId: userStore.id,
  });

  const baseContent = selectedResponse.value?.content || '';
  responseReviewWorkingHtml.value = embedOpinionBoxIntoHtml(baseContent, leaderBoxHtml, reviewActionForm.placement);
  responseReviewStep.value = 'position';

  nextTick(() => {
    setupResponseReviewPositionDraggable();
  });
};

const resetResponseReviewPosition = () => {
  const isDgLeader = isDg.value || reviewActionForm.action === 'DG_APPROVE';
  const reviewerName = userStore.name_kh || userStore.name || '';
  const reviewerSig = userStore.signature_image || '';
  const shortDateKh = reviewActionForm.sign_date || getShortKhmerDateString(new Date());
  const comment = reviewActionForm.comment || (isDgLeader ? 'បានពិនិត្យ និងសម្រេចឯកភាព' : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច');

  const leaderBoxHtml = generateDraggableOpinionBoxHtml({
    comment,
    reviewerName,
    signatureUrl: reviewerSig,
    shortDateKh,
    ownerId: userStore.id,
  });

  const baseContent = selectedResponse.value?.content || '';
  responseReviewWorkingHtml.value = embedOpinionBoxIntoHtml(baseContent, leaderBoxHtml, reviewActionForm.placement);

  nextTick(() => {
    setupResponseReviewPositionDraggable();
  });
};

const submitReviewAction = async () => {
  if (document.activeElement && typeof document.activeElement.blur === 'function') {
    document.activeElement.blur();
  }
  if (reviewActionForm.action === 'FORWARD' && !reviewActionForm.forwarded_to_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសថ្នាក់ដឹកនាំដើម្បីចារឆ្លងបន្ត!' });
    return;
  }

  // If in step 1 and embed is selected, transition to position step first
  if (responseReviewStep.value === 'form' && reviewActionForm.embed_into_document && selectedResponse.value?.content && reviewActionForm.action !== 'RETURN') {
    goToResponseReviewPositionStep();
    return;
  }

  submittingReview.value = true;
  try {
    let finalContent = undefined;
    if (reviewActionForm.embed_into_document && selectedResponse.value && selectedResponse.value.content && reviewActionForm.action !== 'RETURN') {
      if (responseReviewPositionPaperRef.value) {
        const clone = responseReviewPositionPaperRef.value.cloneNode(true);
        clone.querySelectorAll('.draggable-opinion-box').forEach(b => {
          b.classList.remove('is-selected', 'is-dragging', 'is-resizing', 'is-locked-box', 'is-editable-box');
          b.removeAttribute('title');
          b.querySelectorAll('div').forEach(d => {
            if (d.textContent.trim() === '(ហត្ថលេខា)') {
              d.remove();
            }
          });
          b.querySelectorAll('[contenteditable]').forEach(el => {
            el.removeAttribute('contenteditable');
            el.removeAttribute('title');
          });
        });
        finalContent = clone.innerHTML;
      } else {
        const isDgLeader = isDg.value || reviewActionForm.action === 'DG_APPROVE';
        const reviewerName = userStore.name_kh || userStore.name || '';
        const reviewerSig = userStore.signature_image || '';
        const shortDateKh = reviewActionForm.sign_date || getShortKhmerDateString(new Date());
        const comment = reviewActionForm.comment || (isDgLeader ? 'បានពិនិត្យ និងសម្រេចឯកភាព' : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច');

        const leaderBoxHtml = generateDraggableOpinionBoxHtml({
          comment,
          reviewerName,
          signatureUrl: reviewerSig,
          shortDateKh,
          ownerId: userStore.id,
        });

        finalContent = embedOpinionBoxIntoHtml(selectedResponse.value.content, leaderBoxHtml, reviewActionForm.placement);
      }
    }

    if (selectedResponse.value?.id) {
      await apiReviewOutboundAction(selectedResponse.value.id, {
        action: reviewActionForm.action,
        forwarded_to_id: reviewActionForm.forwarded_to_id || undefined,
        response_number: reviewActionForm.response_number || undefined,
        comment: reviewActionForm.comment || undefined,
        content: finalContent || undefined,
      });
    } else {
      const formData = new FormData();
      formData.append('action', reviewActionForm.action);
      if (reviewActionForm.comment) formData.append('comment', reviewActionForm.comment);
      if (reviewActionForm.forwarded_to_id) formData.append('forwarded_to_id', reviewActionForm.forwarded_to_id);
      if (reviewActionForm.response_number) formData.append('response_number', reviewActionForm.response_number);
      await apiProcessResponseAction(selectedDoc.value.id, formData);
    }

    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានអនុវត្តសកម្មភាពពិនិត្យឆ្លងដោយជោគជ័យ!',
      timer: 1800,
      showConfirmButton: false,
    });

    if (cleanupResponseReviewDraggable) {
      cleanupResponseReviewDraggable();
      cleanupResponseReviewDraggable = null;
    }
    showReviewResponseModal.value = false;
    responseReviewStep.value = 'form';
    loadDocuments();
    loadStats();
  } catch (err) {
    const errorMsg = err.response?.data?.message || Object.values(err.response?.data?.errors || {})[0]?.[0] || 'មិនអាចដំណើរការសកម្មភាពបានឡើយ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: errorMsg });
  } finally {
    submittingReview.value = false;
  }
};

const saveAndHoldResponseReview = async () => {
  if (document.activeElement && typeof document.activeElement.blur === 'function') {
    document.activeElement.blur();
  }
  submittingReview.value = true;
  try {
    let finalContent = undefined;
    if (responseReviewPositionPaperRef.value) {
      const clone = responseReviewPositionPaperRef.value.cloneNode(true);
      clone.querySelectorAll('.draggable-opinion-box').forEach(b => {
        b.classList.remove('is-selected', 'is-dragging', 'is-resizing', 'is-locked-box', 'is-editable-box');
        b.removeAttribute('title');
        b.querySelectorAll('*').forEach(d => {
          if (d.textContent.trim() === '(ហត្ថលេខា)') {
            d.remove();
          }
        });
        b.querySelectorAll('[contenteditable]').forEach(el => {
          el.removeAttribute('contenteditable');
          el.removeAttribute('title');
        });
      });
      finalContent = clone.innerHTML;
    } else if (reviewActionForm.embed_into_document && selectedResponse.value && selectedResponse.value.content) {
      const isDgLeader = isDg.value || reviewActionForm.action === 'DG_APPROVE';
      const reviewerName = userStore.name_kh || userStore.name || '';
      const reviewerSig = userStore.signature_image || '';
      const shortDateKh = reviewActionForm.sign_date || getShortKhmerDateString(new Date());
      const comment = reviewActionForm.comment || (isDgLeader ? 'បានពិនិត្យ និងសម្រេចឯកភាព' : 'សូមគោរពជូនឯកឧត្តមអគ្គនាយក\nមេត្តាពិនិត្យ និងសម្រេច');

      const leaderBoxHtml = generateDraggableOpinionBoxHtml({
        comment,
        reviewerName,
        signatureUrl: reviewerSig,
        shortDateKh,
        ownerId: userStore.id,
      });

      finalContent = embedOpinionBoxIntoHtml(selectedResponse.value.content, leaderBoxHtml, reviewActionForm.placement);
    }

    if (selectedResponse.value?.id) {
      await apiReviewOutboundAction(selectedResponse.value.id, {
        action: 'HOLD',
        comment: reviewActionForm.comment || undefined,
        content: finalContent || undefined,
      });
    }

    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានរក្សាទុកមតិយោបល់ និងខ្លឹមសារក្នុងដៃដោយជោគជ័យ! លោកអ្នកអាចចារឆ្លងទៅថ្នាក់លើនៅពេលក្រោយ។',
      timer: 2000,
      showConfirmButton: false,
    });

    if (cleanupResponseReviewDraggable) {
      cleanupResponseReviewDraggable();
      cleanupResponseReviewDraggable = null;
    }
    showReviewResponseModal.value = false;
    responseReviewStep.value = 'form';
    loadDocuments();
    loadStats();
  } catch (err) {
    const errorMsg = err.response?.data?.message || 'មិនអាចរក្សាទុកក្នុងដៃបានឡើយ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: errorMsg });
  } finally {
    submittingReview.value = false;
  }
};

const openDetailModal = async (doc) => {
  detailFlowTab.value = 'inbound';
  try {
    const res = await apiGetInboundDocument(doc.id);
    selectedDoc.value = res.data;
    showDetailModal.value = true;
  } catch (err) {
    selectedDoc.value = doc;
    showDetailModal.value = true;
  }
};

const deleteDocumentAction = async (doc) => {
  if (!doc) return;
  const docRef = doc.general_inbound_number ? `លេខ ${doc.general_inbound_number}` : (doc.title ? `«${doc.title}»` : '');
  const confirm = await Swal.fire({
    title: 'លុបឯកសារចូល?',
    text: `តើលោកអ្នកពិតជាចង់លុបឯកសារ${docRef ? ' ' + docRef : ''} នេះមែនទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានឡើយ!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'លុបចេញ',
    cancelButtonText: 'បោះបង់',
    confirmButtonColor: '#dc3545',
  });
  if (!confirm.isConfirmed) return;

  try {
    await apiDeleteInboundDocument(doc.id);
    Swal.fire({ icon: 'success', title: 'បានលុបឯកសារដោយជោគជ័យ!', timer: 1500, showConfirmButton: false });
    if (showDetailModal.value) {
      showDetailModal.value = false;
    }
    loadDocuments();
    loadStats();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចលុបឯកសារបានឡើយ!' });
  }
};

// --- Telegram Settings Methods ---
const openTelegramModal = () => {
  telegramForm.chat_id = userStore.user?.telegram_chat_id || '';
  telegramForm.username = userStore.user?.telegram_username || '';
  showTelegramModal.value = true;
};

const testTelegramConnection = async () => {
  if (!telegramForm.chat_id) {
    Swal.fire({
      icon: 'warning',
      title: 'សូមបញ្ចូល Telegram Chat ID',
      text: 'សូមបញ្ចូល Chat ID ជាមុនសិនដើម្បីសាកល្បងផ្ញើសារ!'
    });
    return;
  }

  testingTelegram.value = true;
  try {
    const res = await apiTestTelegramConnection({ chat_id: telegramForm.chat_id });
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: res.data?.message || 'សារសាកល្បងត្រូវបានផ្ញើទៅកាន់ Telegram របស់អ្នកដោយជោគជ័យ!',
      confirmButtonText: 'យល់ព្រម',
    });
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ!',
      text: err.response?.data?.message || 'មិនអាចផ្ញើសារបានឡើយ! សូមពិនិត្យមើល Chat ID និងប្រាកដថាអ្នកបានចុច Start Bot @trms_regulator_bot រួចរាល់។',
      confirmButtonText: 'យល់ព្រម',
    });
  } finally {
    testingTelegram.value = false;
  }
};

const saveTelegramSettings = async () => {
  savingTelegram.value = true;
  try {
    const res = await apiUpdateTelegramSettings({
      telegram_chat_id: telegramForm.chat_id,
      telegram_username: telegramForm.username,
    });

    if (userStore.user) {
      userStore.user.telegram_chat_id = telegramForm.chat_id;
      userStore.user.telegram_username = telegramForm.username;
    }

    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: res.data?.message || 'បានរក្សាទុកព័ត៌មាន Telegram ដោយជោគជ័យ!',
      timer: 1500,
      showConfirmButton: false,
    });
    showTelegramModal.value = false;
  } catch (err) {
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ!',
      text: err.response?.data?.message || 'មិនអាចរក្សាទុកព័ត៌មាន Telegram បានឡើយ!',
    });
  } finally {
    savingTelegram.value = false;
  }
};

// --- PDF Preview & Download Helpers ---
const previewOriginalDoc = (doc) => {
  const title = `ឯកសារដើម៖ ${doc.general_inbound_number || ''} - ${doc.title || ''}`;
  openPdfPreview(title, getViewOriginalUrl(doc.id), getOriginalDownloadUrl(doc.id));
};

const previewAnnotatedDoc = (doc) => {
  const title = `ឯកសារមានចំណារ៖ ${doc.general_inbound_number || ''} - ${doc.title || ''}`;
  openPdfPreview(title, getViewAnnotatedUrl(doc.id), getAnnotatedDownloadUrl(doc.id));
};

const previewResponseDoc = (resp) => {
  const title = `ឯកសារឆ្លើយតប៖ ${resp.title || ''}`;
  openPdfPreview(title, getViewResponseUrl(resp.id), getResponseDownloadUrl(resp.id));
};

const downloadOriginal = (doc) => {
  window.open(getOriginalDownloadUrl(doc.id), '_blank');
};

const getOriginalFileUrl = (id) => getOriginalDownloadUrl(id);
const getAnnotatedFileUrl = (id) => getAnnotatedDownloadUrl(id);
const getResponseFileUrl = (responseId) => getResponseDownloadUrl(responseId);

// --- Formatters ---
const formatDateKh = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d)) return dateStr;
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  return `${day}/${month}/${year}`;
};

const formatDateTimeKh = (dateTimeStr) => {
  if (!dateTimeStr) return '';
  const d = new Date(dateTimeStr);
  if (isNaN(d)) return dateTimeStr;
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  const hours = String(d.getHours()).padStart(2, '0');
  const mins = String(d.getMinutes()).padStart(2, '0');
  return `${day}/${month}/${year} ${hours}:${mins}`;
};

const getStatusBadgeClass = (status) => {
  return matchStatusClass(status);
};

const matchStatusClass = (status) => {
  switch (status) {
    case 'RECEPTION_DRAFT':
      return 'badge-warning text-dark';
    case 'SUBMITTED_TO_ASSISTANT':
      return 'badge-primary';
    case 'SUBMITTED_TO_DG':
      return 'badge-danger';
    case 'DG_ANNOTATED':
      return 'badge-secondary';
    case 'DISPATCHED':
      return 'badge-info';
    case 'IN_RESPONSE_PROGRESS':
      return 'badge-warning text-dark';
    case 'COMPLETED':
      return 'badge-success';
    case 'CANCELLED':
      return 'badge-dark';
    default:
      return 'badge-secondary';
  }
};

onMounted(() => {
  syncTabFromRoute();
  loadDocuments();
  loadStats();
  loadRecipientsOptions();
  loadSenderOrganizations();
});
</script>

<style scoped>
.khmer-layout {
  font-family: 'Kantumruy Pro', 'Battambang', 'Hanuman', 'Siemreap', sans-serif;
}

.font-khmer {
  font-family: 'Kantumruy Pro', 'Battambang', 'Hanuman', 'Siemreap', sans-serif !important;
}

.khmer-page-title {
  font-size: 1.4rem;
}

.btn-dark-custom {
  background-color: #1e293b;
  color: #ffffff;
  border-color: #1e293b;
}
.btn-dark-custom:hover {
  background-color: #0f172a;
  color: #ffffff;
}

/* Stat Cards */
.stat-card {
  transition: all 0.2s ease-in-out;
}
.stat-card:hover {
  transform: translateY(-2px);
}
.active-stat-card {
  border-bottom: 3px solid !important;
}

/* Mini Stat Cards */
.stat-mini-card {
  position: relative;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px 14px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
  user-select: none;
}

.stat-mini-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 18px -4px rgba(0, 0, 0, 0.08) !important;
  border-color: #cbd5e1;
}

.stat-mini-content {
  flex: 1;
  min-width: 0;
}

.stat-mini-title {
  font-size: 11.5px;
  font-weight: 600;
  color: #64748b;
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stat-mini-number {
  font-size: 1.4rem;
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -0.5px;
}

.stat-mini-tag {
  font-size: 11px;
  font-weight: 600;
  padding: 1px 6px;
  border-radius: 6px;
  background: rgba(0, 0, 0, 0.04);
}

.stat-mini-icon {
  width: 40px;
  height: 40px;
  min-width: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  margin-left: 10px;
  transition: transform 0.2s ease;
}

.stat-mini-card:hover .stat-mini-icon {
  transform: scale(1.08);
}

.stat-mini-bar {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 3px;
  opacity: 0.35;
  transition: opacity 0.2s ease, height 0.2s ease;
}

.stat-mini-card:hover .stat-mini-bar {
  opacity: 0.8;
  height: 4px;
}

.stat-mini-card.is-active .stat-mini-bar {
  opacity: 1;
  height: 4px;
}

/* Color variations */
/* Cyan */
.stat-mini-cyan.is-active {
  background-color: #f0f9ff;
  border-color: #38bdf8;
  box-shadow: 0 4px 14px rgba(14, 165, 233, 0.15) !important;
}
.text-cyan { color: #0284c7 !important; }
.bg-cyan { background-color: #0ea5e9 !important; }
.bg-cyan-subtle { background-color: #e0f2fe !important; }

/* Indigo */
.stat-mini-indigo.is-active {
  background-color: #eef2ff;
  border-color: #818cf8;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.15) !important;
}
.text-indigo { color: #4f46e5 !important; }
.bg-indigo { background-color: #6366f1 !important; }
.bg-indigo-subtle { background-color: #e0e7ff !important; }

/* Rose */
.stat-mini-rose.is-active {
  background-color: #fff1f2;
  border-color: #fb7185;
  box-shadow: 0 4px 14px rgba(244, 63, 94, 0.15) !important;
}
.text-rose { color: #e11d48 !important; }
.bg-rose { background-color: #f43f5e !important; }
.bg-rose-subtle { background-color: #ffe4e6 !important; }

/* Teal */
.stat-mini-teal.is-active {
  background-color: #f0fdfa;
  border-color: #2dd4bf;
  box-shadow: 0 4px 14px rgba(13, 148, 136, 0.15) !important;
}
.text-teal { color: #0d9488 !important; }
.bg-teal { background-color: #14b8a6 !important; }
.bg-teal-subtle { background-color: #ccfbf1 !important; }

/* Amber */
.stat-mini-amber.is-active {
  background-color: #fffbeb;
  border-color: #f59e0b;
  box-shadow: 0 4px 14px rgba(217, 119, 6, 0.15) !important;
}
.text-amber { color: #d97706 !important; }
.bg-amber { background-color: #f59e0b !important; }
.bg-amber-subtle { background-color: #fef3c7 !important; }

/* Red */
.stat-mini-red.is-active {
  background-color: #fef2f2;
  border-color: #ef4444;
  box-shadow: 0 4px 14px rgba(220, 38, 38, 0.18) !important;
}
.text-red { color: #dc2626 !important; }
.bg-red { background-color: #ef4444 !important; }
.bg-red-subtle { background-color: #fee2e2 !important; }

/* Pulse animation for Overdue badge */
.pulse-badge {
  animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
  0% { transform: scale(1); }
  50% { transform: scale(1.06); background-color: #fee2e2; }
  100% { transform: scale(1); }
}

.stat-icon-circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-info-light { background-color: #e0f2fe; }
.bg-warning-light { background-color: #fef3c7; }
.bg-primary-light { background-color: #e0e7ff; }
.bg-danger-light { background-color: #fee2e2; }
.bg-success-light { background-color: #dcfce7; }

/* Font size utils */
.font-11 { font-size: 11px; }
.font-12 { font-size: 12px; }
.font-13 { font-size: 13px; }
.font-14 { font-size: 14px; }
.font-16 { font-size: 16px; }
.font-18 { font-size: 18px; }
.font-20 { font-size: 20px; }

.shadow-2xs {
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.cursor-pointer {
  cursor: pointer;
}

.hover-underline:hover {
  text-decoration: underline;
}

.max-h-500 {
  max-height: 500px;
}

/* Modals */
.custom-modal-backdrop,
:deep(.custom-modal-backdrop) {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  bottom: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  background-color: rgba(0, 0, 0, 0.6) !important;
  display: flex !important;
  align-items: flex-start !important;
  justify-content: center !important;
  z-index: 1060 !important;
  overflow-y: auto !important;
  overflow-x: hidden !important;
  padding: 2rem 1rem !important;
}

.custom-modal-backdrop .modal-dialog,
:deep(.custom-modal-backdrop .modal-dialog) {
  margin: auto !important;
  max-height: 92vh;
}

.custom-modal-backdrop .modal-content,
:deep(.custom-modal-backdrop .modal-content) {
  border-radius: 10px;
  max-height: 90vh;
}

.shadow-2xl {
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35) !important;
}

/* Timeline */
.timeline-step {
  border-left-width: 2px !important;
}
.timeline-step::before {
  content: '';
  position: absolute;
  left: -6px;
  top: 4px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: #3b82f6;
}

/* FontAwesome safeguard */
.fas, :deep(.fas), .fa, :deep(.fa) {
  font-family: "Font Awesome 5 Free" !important;
  font-weight: 900 !important;
  font-style: normal !important;
}
.far, :deep(.far) {
  font-family: "Font Awesome 5 Free" !important;
  font-weight: 400 !important;
  font-style: normal !important;
}

.hover-bg-light:hover {
  background-color: #f1f5f9;
}
.py-1-5 {
  padding-top: 0.375rem;
  padding-bottom: 0.375rem;
}
.ring-1 {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
}

/* Custom Status & Desk Badges */
.badge-indigo {
  background-color: #6366f1 !important;
  color: #fff !important;
}
.text-indigo {
  color: #6366f1 !important;
}
.badge-rose {
  background-color: #f43f5e !important;
  color: #fff !important;
}
.text-rose {
  color: #f43f5e !important;
}
.badge-teal {
  background-color: #0d9488 !important;
  color: #fff !important;
}
.text-teal {
  color: #0d9488 !important;
}

/* Custodian Avatar Styles */
.custodian-avatar {
  width: 36px;
  height: 36px;
  min-width: 36px;
  min-height: 36px;
  object-fit: cover;
  border: 2px solid #0056b3 !important;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  cursor: pointer;
}
.custodian-avatar:hover {
  transform: scale(1.15);
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.22) !important;
  z-index: 10;
}
.custodian-avatar-fallback {
  width: 36px;
  height: 36px;
  min-width: 36px;
  min-height: 36px;
  background: linear-gradient(135deg, #0056b3, #17a2b8);
  font-size: 14px;
  border: 2px solid #ffffff;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
  cursor: pointer;
  transition: transform 0.2s ease;
}
.custodian-avatar-fallback:hover {
  transform: scale(1.15);
}

/* Draggable Leadership Opinion Box & Handles */
:deep(.draggable-opinion-box) {
  position: relative;
  border: 1px dashed transparent !important;
  background: transparent !important;
  vertical-align: top !important;
  transition: box-shadow 0.15s ease;
}

:deep(.draggable-opinion-box:hover),
:deep(.draggable-opinion-box.is-selected) {
  outline: 1.5px dashed #2563eb !important;
  outline-offset: 2px;
}

:deep(.draggable-opinion-box:hover .box-handle),
:deep(.draggable-opinion-box.is-selected .box-handle) {
  display: block !important;
}

:deep(.draggable-opinion-box.is-dragging) {
  opacity: 0.92;
  box-shadow: 0 8px 24px rgba(37, 99, 235, 0.25) !important;
  cursor: grabbing !important;
  z-index: 100 !important;
}

:deep(.draggable-opinion-box.is-resizing) {
  opacity: 0.95;
  outline: 1.5px dashed #2563eb !important;
  user-select: none !important;
  z-index: 100 !important;
}

:deep(.draggable-opinion-box.is-locked-box) {
  cursor: default !important;
  outline: none !important;
  border: 1px dashed transparent !important;
}
:deep(.draggable-opinion-box.is-locked-box:hover) {
  outline: none !important;
}
:deep(.draggable-opinion-box.is-locked-box .box-handle) {
  display: none !important;
}

/* Ensure report signature table always aligns cells to the top row */
:deep(.drafter-signature-table) {
  width: 100% !important;
  border-collapse: collapse !important;
}
:deep(.drafter-signature-table tr) {
  vertical-align: top !important;
}
:deep(.drafter-signature-table td) {
  vertical-align: top !important;
}
:deep(.drafter-signature-table .opinion-left-col) {
  width: 50% !important;
  vertical-align: top !important;
  text-align: center !important;
}

:deep(.box-handle) {
  display: none;
  position: absolute;
  width: 8px;
  height: 8px;
  background-color: #2563eb;
  border: 1px solid #ffffff;
  box-sizing: border-box;
  z-index: 30;
  border-radius: 1px;
}
:deep(.box-handle.handle-tl) { top: -4.5px; left: -4.5px; cursor: nwse-resize; }
:deep(.box-handle.handle-tc) { top: -4.5px; left: calc(50% - 4px); cursor: ns-resize; }
:deep(.box-handle.handle-tr) { top: -4.5px; right: -4.5px; cursor: nesw-resize; }
:deep(.box-handle.handle-ml) { top: calc(50% - 4px); left: -4.5px; cursor: ew-resize; }
:deep(.box-handle.handle-mr) { top: calc(50% - 4px); right: -4.5px; cursor: ew-resize; }
:deep(.box-handle.handle-bl) { bottom: -4.5px; left: -4.5px; cursor: nesw-resize; }
:deep(.box-handle.handle-bc) { bottom: -4.5px; left: calc(50% - 4px); cursor: ns-resize; }
:deep(.box-handle.handle-br) { bottom: -4.5px; right: -4.5px; cursor: nwse-resize; }

:deep(.opinion-box-comment[contenteditable="true"]:hover),
:deep(.opinion-box-date[contenteditable="true"]:hover) {
  background-color: #f1f5f9;
  border-radius: 2px;
  cursor: text;
}

:deep(.opinion-box-comment[contenteditable="true"]:focus),
:deep(.opinion-box-date[contenteditable="true"]:focus) {
  outline: 1.5px dashed #2563eb !important;
  background-color: #ffffff !important;
  border-radius: 2px;
  padding: 1px 3px;
  cursor: text;
}

@media print {
  :deep(.box-handle),
  :deep(.draggable-opinion-box .box-handle) {
    display: none !important;
  }
  :deep(.draggable-opinion-box) {
    outline: none !important;
    box-shadow: none !important;
  }
}
</style>

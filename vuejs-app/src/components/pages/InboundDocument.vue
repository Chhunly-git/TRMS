<template>
  <div class="content-wrapper khmer-layout">
    <!-- 1. Header Section -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-3 align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0 font-weight-bold text-dark khmer-page-title">
              <i class="fas fa-file-import text-success mr-2"></i>គ្រប់គ្រងលំហូរឯកសារចូល (ន.ប.ធ.)
            </h1>
            <p class="text-muted small mb-0 mt-1">
              តាមដាន និងចាត់ចែងលំហូរឯកសារចូល ចាប់ពីការចុះលេខទូទៅ លេខជំនួយការអគ្គនាយក ចំណារ រហូតដល់ការឆ្លើយតប
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
              <i class="fas fa-plus-circle mr-1"></i> ចុះបញ្ជីឯកសារចូលថ្មី
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
          <!-- Card 1: ឯកសារសរុប -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div
              class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
              :class="{ 'active-stat-card border-primary': activeTab === 'all' }"
              @click="switchTab('all')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">
                    {{ isRegularOfficer ? 'ឯកសារទទួលបានសរុប' : 'ឯកសារចូលសរុប' }}
                  </span>
                  <h3 class="font-weight-bold mb-0 text-dark">{{ stats.total || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-info-light text-info">
                  <i class="fas fa-folder-open fa-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 2: ឯកសារត្រូវធ្វើ (To-Do) -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div
              class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
              :class="{ 'active-stat-card border-warning': activeTab === 'my_todo' }"
              @click="switchTab('my_todo')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">ឯកសារត្រូវធ្វើ (To-Do)</span>
                  <h3 class="font-weight-bold mb-0 text-warning">{{ stats.my_todo || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-warning-light text-warning">
                  <i class="fas fa-clipboard-check fa-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 3: អង្គភាពរបស់ខ្ញុំ (My Unit) -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div
              class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
              :class="{ 'active-stat-card border-primary': activeTab === 'my_unit' }"
              @click="switchTab('my_unit')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">អង្គភាពរបស់ខ្ញុំ</span>
                  <h3 class="font-weight-bold mb-0 text-primary">{{ stats.my_unit || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-primary-light text-primary">
                  <i class="fas fa-sitemap fa-lg"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 4: បានធ្វើរួច (Done) -->
          <div class="col-xl-3 col-md-6 col-sm-6 mb-3">
            <div
              class="card stat-card shadow-sm border-0 rounded-lg h-100 cursor-pointer transition-all"
              :class="{ 'active-stat-card border-success': activeTab === 'my_done' }"
              @click="switchTab('my_done')"
            >
              <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                  <span class="text-muted small d-block font-weight-bold">បានធ្វើរួច (Done)</span>
                  <h3 class="font-weight-bold mb-0 text-success">{{ stats.my_done || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-success-light text-success">
                  <i class="fas fa-check-double fa-lg"></i>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Secondary Stats Row with Desks and Deadline Counters -->
        <div class="row mb-3">
          <!-- Card 5: អ្នកទទួលឯកសារ (Reception Desk) -->
          <div class="col-xl col-md-4 col-sm-6 mb-2" v-if="canRegister || isAdmin">
            <div
              class="stat-mini-card stat-mini-cyan shadow-sm h-100 cursor-pointer"
              :class="{ 'is-active': activeTab === 'reception' }"
              @click="switchTab('reception')"
              title="ចុចដើម្បីមើលឯកសារនៅអ្នកទទួលឯកសារ"
            >
              <div class="stat-mini-content">
                <span class="stat-mini-title">អ្នកទទួល (មិនទាន់បញ្ជូន)</span>
                <div class="d-flex align-items-baseline mt-1">
                  <span class="stat-mini-number text-cyan">{{ stats.reception_pending || 0 }}</span>
                  <span class="stat-mini-tag ml-2 text-cyan">រង់ចាំបញ្ជូន</span>
                </div>
              </div>
              <div class="stat-mini-icon bg-cyan-subtle text-cyan">
                <i class="fas fa-inbox"></i>
              </div>
              <div class="stat-mini-bar bg-cyan"></div>
            </div>
          </div>

          <!-- Card 6: ជំនួយការអគ្គនាយក (Assistant Desk) -->
          <div class="col-xl col-md-4 col-sm-6 mb-2" v-if="canAssist || isAdmin">
            <div
              class="stat-mini-card stat-mini-indigo shadow-sm h-100 cursor-pointer"
              :class="{ 'is-active': activeTab === 'assistant_inbox' }"
              @click="switchTab('assistant_inbox')"
              title="ចុចដើម្បីមើលឯកសារនៅជំនួយការអគ្គនាយក"
            >
              <div class="stat-mini-content">
                <span class="stat-mini-title">ជំនួយការអគ្គនាយក</span>
                <div class="d-flex align-items-baseline mt-1">
                  <span class="stat-mini-number text-indigo">{{ stats.assistant_pending || 0 }}</span>
                  <span class="stat-mini-tag ml-2 text-indigo">រង់ចាំចុះលេខ</span>
                </div>
              </div>
              <div class="stat-mini-icon bg-indigo-subtle text-indigo">
                <i class="fas fa-user-shield"></i>
              </div>
              <div class="stat-mini-bar bg-indigo"></div>
            </div>
          </div>

          <!-- Card 7: ដាក់ជូនអគ្គនាយក (DG Desk) -->
          <div class="col-xl col-md-4 col-sm-6 mb-2" v-if="isDg || canAssist || isAdmin">
            <div
              class="stat-mini-card stat-mini-rose shadow-sm h-100 cursor-pointer"
              :class="{ 'is-active': activeTab === 'dg_inbox' }"
              @click="switchTab('dg_inbox')"
              title="ចុចដើម្បីមើលឯកសាររង់ចាំចំណារអគ្គនាយក"
            >
              <div class="stat-mini-content">
                <span class="stat-mini-title">រង់ចាំចំណារអគ្គនាយក</span>
                <div class="d-flex align-items-baseline mt-1">
                  <span class="stat-mini-number text-rose">{{ stats.dg_pending || 0 }}</span>
                  <span class="stat-mini-tag ml-2 text-rose">តុអគ្គនាយក</span>
                </div>
              </div>
              <div class="stat-mini-icon bg-rose-subtle text-rose">
                <i class="fas fa-pen-nib"></i>
              </div>
              <div class="stat-mini-bar bg-rose"></div>
            </div>
          </div>

          <!-- Card 8: បានចែកចាយ -->
          <div class="col-xl col-md-4 col-sm-6 mb-2">
            <div
              class="stat-mini-card stat-mini-teal shadow-sm h-100 cursor-pointer"
              :class="{ 'is-active': filters.status === 'dispatched_all' }"
              @click="toggleStatusFilter('dispatched_all')"
              title="ចុចដើម្បីបង្ហាញឯកសារដែលបានចែកចាយ"
            >
              <div class="stat-mini-content">
                <span class="stat-mini-title">បានចែកចាយបន្ត</span>
                <div class="d-flex align-items-baseline mt-1">
                  <span class="stat-mini-number text-teal">{{ stats.dispatched || 0 }}</span>
                  <span class="stat-mini-tag ml-2 text-teal">តាមអង្គភាព</span>
                </div>
              </div>
              <div class="stat-mini-icon bg-teal-subtle text-teal">
                <i class="fas fa-share-alt"></i>
              </div>
              <div class="stat-mini-bar bg-teal"></div>
            </div>
          </div>

          <!-- Card 9: ជិតដល់កាលកំណត់ (Due Soon <= 3 days) -->
          <div class="col-xl col-md-4 col-sm-6 mb-2">
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

          <!-- Card 10: ហួសកាលកំណត់ (Overdue) -->
          <div class="col-xl col-md-4 col-sm-6 mb-2">
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
            <ul class="nav nav-tabs border-0 px-3 pt-2">
              <!-- Tab 1: ត្រូវធ្វើ (To-Do) - For everyone -->
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'my_todo' }"
                  href="javascript:void(0)"
                  @click="switchTab('my_todo')"
                >
                  <i class="fas fa-clipboard-check text-warning mr-1"></i> ឯកសារត្រូវធ្វើ
                  <span class="badge badge-warning ml-1" v-if="stats.my_todo > 0">{{ stats.my_todo }}</span>
                </a>
              </li>

              <!-- Tab 2: អង្គភាពរបស់ខ្ញុំ (My Unit) - For Department / Office leaders & members -->
              <li class="nav-item" v-if="userStore.department_id || userStore.office_id">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'my_unit' }"
                  href="javascript:void(0)"
                  @click="switchTab('my_unit')"
                >
                  <i class="fas fa-sitemap text-primary mr-1"></i> អង្គភាពរបស់ខ្ញុំ
                  <span class="badge badge-primary ml-1" v-if="stats.my_unit > 0">{{ stats.my_unit }}</span>
                </a>
              </li>

              <!-- Tab 3: បានធ្វើរួច (Done) - For everyone -->
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'my_done' }"
                  href="javascript:void(0)"
                  @click="switchTab('my_done')"
                >
                  <i class="fas fa-check-double text-success mr-1"></i> បានធ្វើរួច
                  <span class="badge badge-success ml-1" v-if="stats.my_done > 0">{{ stats.my_done }}</span>
                </a>
              </li>

              <!-- Tab 4: ឯកសារទាំងអស់ (All) / ឯកសារទទួលបានទាំងអស់ -->
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'all' }"
                  href="javascript:void(0)"
                  @click="switchTab('all')"
                >
                  <i class="fas fa-th-list mr-1"></i> {{ isRegularOfficer ? 'ឯកសារទទួលបានទាំងអស់' : 'ឯកសារទាំងអស់' }}
                  <span class="badge badge-light ml-1">{{ stats.total || 0 }}</span>
                </a>
              </li>

              <!-- Tab 5: អ្នកទទួលឯកសារ (Reception) -->
              <li class="nav-item" v-if="canRegister">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'reception' }"
                  href="javascript:void(0)"
                  @click="switchTab('reception')"
                >
                  <i class="fas fa-inbox mr-1 text-info"></i> អ្នកទទួលឯកសារ
                  <span class="badge badge-info ml-1" v-if="stats.reception_pending > 0">{{ stats.reception_pending }}</span>
                </a>
              </li>

              <!-- Tab 6: ជំនួយការអគ្គនាយក (Assistant Desk) -->
              <li class="nav-item" v-if="canAssist">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'assistant_inbox' }"
                  href="javascript:void(0)"
                  @click="switchTab('assistant_inbox')"
                >
                  <i class="fas fa-user-shield mr-1 text-danger"></i> ជំនួយការអគ្គនាយក
                  <span class="badge badge-danger ml-1" v-if="stats.assistant_pending > 0">{{ stats.assistant_pending }}</span>
                </a>
              </li>

              <!-- Tab 7: ដាក់ជូនអគ្គនាយក (DG Desk) -->
              <li class="nav-item" v-if="isDg || isAdmin">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'dg_inbox' }"
                  href="javascript:void(0)"
                  @click="switchTab('dg_inbox')"
                >
                  <i class="fas fa-pen-nib mr-1 text-secondary"></i> ដាក់ជូនអគ្គនាយក
                  <span class="badge badge-secondary ml-1" v-if="stats.dg_pending > 0">{{ stats.dg_pending }}</span>
                </a>
              </li>
            </ul>
          </div>

          <!-- Filter & Search Toolbar -->
          <div class="card-body p-3 bg-light-subtle">
            <div class="row align-items-center">
              <!-- Search Box -->
              <div class="col-lg-3 col-md-6 mb-2">
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

              <!-- DG Category Filter -->
              <div class="col-lg-2 col-md-3 col-sm-6 mb-2">
                <select class="form-control form-control-sm font-khmer" v-model="filters.dg_inbound_category" @change="loadDocuments">
                  <option value="">-- ប្រភេទលេខចូល អ.ន. --</option>
                  <option value="COMPANY">ក្រុមហ៊ុន (AA)</option>
                  <option value="MEF">ក្រសួងសេដ្ឋកិច្ច (E)</option>
                  <option value="FSA_REGULATOR">អ.ស.ហ. និងនិយ័តករ (NF)</option>
                  <option value="DEPT_GENERAL_AFFAIRS">នាយកដ្ឋានកិច្ចការទូទៅ (A)</option>
                  <option value="DEPT_REGISTRATION">នាយកដ្ឋានចុះបញ្ជី (R)</option>
                  <option value="DEPT_RESEARCH">នាយកដ្ឋានស្រាវជ្រាវ (T)</option>
                  <option value="DEPT_LEGAL">នាយកដ្ឋានគតិយុត្ត (L)</option>
                  <option value="PROJECT_ACSEP">គម្រោង ACSEP (AS)</option>
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
                    <th style="width: 105px;">លេខចូលទូទៅ</th>
                    <th style="width: 120px;">លេខចូល អ.ន.</th>
                    <th style="min-width: 140px;">កាលបរិច្ឆេទ & អ្នកយក</th>
                    <th style="min-width: 160px;">មកពីអង្គភាព / លេខដើម</th>
                    <th>កម្មវត្ថុ / ប្រភេទឯកសារ</th>
                    <th style="width: 130px;">ស្ថានភាព</th>
                    <th style="min-width: 140px;">ភាគីទទួលបន្ទុក</th>
                    <th style="width: 150px;" class="text-center">សកម្មភាព</th>
                  </tr>
                </thead>
                <tbody class="font-khmer">
                  <!-- Loading State -->
                  <tr v-if="loading">
                    <td colspan="8" class="text-center py-5 text-muted">
                      <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>
                      កំពុងទាញយកទិន្នន័យឯកសារ...
                    </td>
                  </tr>

                  <!-- Empty State -->
                  <tr v-else-if="!documents.data || documents.data.length === 0">
                    <td colspan="8" class="text-center py-5 text-muted">
                      <i class="fas fa-folder-open fa-3x mb-2 text-secondary d-block"></i>
                      មិនមានទិន្នន័យឯកសារចូលនៅក្នុងប្រព័ន្ធឡើយ
                    </td>
                  </tr>

                  <!-- Data Rows -->
                  <tr v-for="doc in documents.data" :key="doc.id">
                    <!-- 1. លេខចូលទូទៅ -->
                    <td>
                      <span class="badge badge-dark font-13 px-2 py-1 shadow-2xs font-monospace">
                        {{ doc.general_inbound_number || '---' }}
                      </span>
                    </td>

                    <!-- 2. លេខចូលជំនួយការអគ្គនាយក -->
                    <td>
                      <span
                        v-if="doc.dg_inbound_number"
                        class="badge badge-primary font-13 px-2 py-1 shadow-2xs font-monospace"
                      >
                        {{ doc.dg_inbound_number }}
                      </span>
                      <span v-else class="text-muted small italic">មិនទាន់ចុះ</span>
                    </td>

                    <!-- 3. កាលបរិច្ឆេទ & អ្នកយក -->
                    <td>
                      <div class="font-weight-bold font-13 text-dark">
                        <i class="far fa-calendar-alt text-muted mr-1"></i>
                        {{ formatDateKh(doc.received_date) }}
                      </div>
                      <div class="small text-muted">
                        <i class="far fa-clock mr-1"></i>{{ doc.received_time ? doc.received_time.substring(0, 5) : '' }}
                      </div>
                      <div class="small text-secondary mt-1">
                        <i class="far fa-user mr-1"></i>{{ doc.deliverer_name }}
                        <span v-if="doc.deliverer_phone">({{ doc.deliverer_phone }})</span>
                      </div>
                    </td>

                    <!-- 4. មកពីអង្គភាព & លេខលិខិតដើម -->
                    <td>
                      <div class="font-weight-bold text-dark font-13">
                        <i class="fas fa-building text-secondary mr-1"></i>
                        {{ doc.sender_organization }}
                      </div>
                      <div class="small text-muted mt-1" v-if="doc.external_reference_number">
                        លេខដើម៖ <span class="font-weight-bold">{{ doc.external_reference_number }}</span>
                        <span v-if="doc.external_document_date"> ({{ formatDateKh(doc.external_document_date) }})</span>
                      </div>
                    </td>

                    <!-- 5. កម្មវត្ថុ & Tags -->
                    <td>
                      <div class="font-weight-bold font-13 mb-1 text-primary cursor-pointer hover-underline" @click="openDetailModal(doc)">
                        {{ doc.title }}
                      </div>
                      <div class="d-flex flex-wrap gap-1 align-items-center">
                        <!-- Type -->
                        <span class="badge badge-light border text-dark font-11">
                          {{ doc.document_type_kh }}
                        </span>

                        <!-- Urgency -->
                        <span
                          v-if="doc.urgency === 'URGENT' || doc.urgency === 'MOST_URGENT'"
                          class="badge font-11"
                          :class="doc.urgency === 'MOST_URGENT' ? 'badge-danger' : 'badge-warning text-dark'"
                        >
                          <i class="fas fa-bolt mr-1"></i>{{ doc.urgency_kh }}
                        </span>

                        <!-- Confidentiality -->
                        <span
                          v-if="doc.confidentiality !== 'NORMAL'"
                          class="badge badge-dark font-11"
                        >
                          <i class="fas fa-shield-alt mr-1"></i>{{ doc.confidentiality_kh }}
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

                    <!-- 6. ស្ថានភាព -->
                    <td>
                      <span class="badge px-2 py-1 font-12 d-inline-block text-wrap" :class="getStatusBadgeClass(doc.status)">
                        {{ doc.status_kh }}
                      </span>
                    </td>

                    <!-- 7. ភាគីទទួលបន្ទុក -->
                    <td>
                      <div v-if="doc.target_user">
                        <span class="font-weight-bold font-12 text-dark d-block">
                          <i class="fas fa-user-circle text-primary mr-1"></i>{{ doc.target_user.name_kh || doc.target_user.name }}
                        </span>
                        <span class="small text-muted" v-if="doc.target_department">
                          {{ doc.target_department.name_kh }}
                        </span>
                      </div>
                      <div v-else-if="doc.target_office">
                        <span class="font-weight-bold font-12 text-dark">
                          <i class="fas fa-door-closed text-info mr-1"></i>{{ doc.target_office.name_kh }}
                        </span>
                      </div>
                      <div v-else-if="doc.target_department">
                        <span class="font-weight-bold font-12 text-dark">
                          <i class="fas fa-building text-secondary mr-1"></i>{{ doc.target_department.name_kh }}
                        </span>
                      </div>
                      <div v-else class="text-muted small">
                        ---
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

                        <!-- Print Routing Slip Button -->
                        <router-link
                          :to="`/inbound-documents/${doc.id}/routing-slip`"
                          target="_blank"
                          class="btn btn-outline-secondary"
                          title="បោះពុម្ពសន្លឹកតាមដានឯកសារ (Routing Slip & QR Code)"
                        >
                          <i class="fas fa-print text-primary"></i>
                        </router-link>

                        <!-- Step 1 -> Step 2: Send to Assistant (by Receptionist) -->
                        <button
                          v-if="canRegister && doc.status === 'RECEPTION_DRAFT'"
                          class="btn btn-outline-primary"
                          title="បញ្ជូនទៅជំនួយការអគ្គនាយក"
                          @click="sendToAssistantAction(doc)"
                        >
                          <i class="fas fa-paper-plane"></i>
                        </button>

                        <!-- Step 2: Assistant Receive & Numbering -->
                        <button
                          v-if="canAssist && doc.status === 'SUBMITTED_TO_ASSISTANT'"
                          class="btn btn-outline-success"
                          title="ជំនួយការចុះលេខចូល អ.ន. & ដាក់ជូនអគ្គនាយក"
                          @click="openAssistantReceiveModal(doc)"
                        >
                          <i class="fas fa-barcode"></i>
                        </button>

                        <!-- Step 3: DG Annotation -->
                        <button
                          v-if="(isDg || canAssist || isAdmin) && doc.status === 'SUBMITTED_TO_DG'"
                          class="btn btn-outline-danger"
                          title="កត់ត្រាចំណារឯកឧត្តមអគ្គនាយក"
                          @click="openDgAnnotateModal(doc)"
                        >
                          <i class="fas fa-signature"></i>
                        </button>

                        <!-- Step 4: Assistant Scan & Dispatch -->
                        <button
                          v-if="canAssist && doc.status === 'DG_ANNOTATED'"
                          class="btn btn-outline-warning text-dark"
                          title="Scan ចំណារ និងចែកចាយបន្ត"
                          @click="openAssistantDispatchModal(doc)"
                        >
                          <i class="fas fa-share-square"></i>
                        </button>

                        <!-- Step 5A: Acknowledge (Path A) -->
                        <button
                          v-if="canAcknowledge(doc)"
                          class="btn btn-outline-success"
                          title="ទទួលជ្រាប និងបញ្ចប់ឯកសារ"
                          @click="acknowledgeAction(doc)"
                        >
                          <i class="fas fa-check-double"></i>
                        </button>

                        <!-- Step 5B: Draft Response (Path B) -->
                        <button
                          v-if="canDraftResponse(doc)"
                          class="btn btn-outline-warning text-dark"
                          title="រៀបចំ និងឆ្លងលិខិតឆ្លើយតប"
                          @click="openSubmitResponseModal(doc)"
                        >
                          <i class="fas fa-reply"></i>
                        </button>

                        <!-- Step 5C: Cascading Forward / Re-assign Downward -->
                        <button
                          v-if="canForward(doc)"
                          class="btn btn-outline-info"
                          title="ចាត់ចែង និងបញ្ជូនបន្តតាមឋានានុក្រម"
                          @click="openForwardModal(doc)"
                        >
                          <i class="fas fa-directions"></i>
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

                        <!-- Download Scans Menu / Button -->
                        <button
                          v-if="doc.original_file_path"
                          class="btn btn-outline-secondary"
                          title="ទាញយកឯកសារ Scan ដើម"
                          @click="downloadOriginal(doc)"
                        >
                          <i class="fas fa-file-pdf text-danger"></i>
                        </button>

                        <!-- Delete Button (Registrar / Admin) -->
                        <button
                          v-if="isAdmin || (canRegister && doc.status === 'RECEPTION_DRAFT')"
                          class="btn btn-outline-danger"
                          title="លុបឯកសារ"
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
    <div class="custom-modal-backdrop" v-if="showCreateModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-dark-custom text-white">
            <h5 class="modal-title font-khmer font-weight-bold">
              <i class="fas fa-file-signature text-warning mr-2"></i>ចុះបញ្ជីឯកសារចូលថ្មី (អ្នកទទួលឯកសារ)
            </h5>
            <button type="button" class="close text-white" @click="closeCreateModal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4 font-khmer">
            <form @submit.prevent="submitCreateDocument">
              <!-- Row 1: លេខចូលទូទៅ (Auto) & កាលបរិច្ឆេទ/ម៉ោង -->
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label font-weight-bold">លេខចូលទូទៅ (Auto) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input
                      type="text"
                      class="form-control font-weight-bold font-monospace bg-light"
                      v-model="createForm.general_inbound_number"
                      readonly
                    />
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="button" @click="fetchNextGeneralNumber" title="ទាញយកលេខថ្មី">
                        <i class="fas fa-sync-alt"></i>
                      </button>
                    </div>
                  </div>
                  <small class="text-muted">ទម្រង់លំនាំដើម៖ 001/26</small>
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

                <div class="col-md-4 mb-3">
                  <label class="form-label font-weight-bold">មកពីអង្គភាព / ស្ថាប័ន <span class="text-danger">*</span></label>
                  <input
                    type="text"
                    class="form-control"
                    placeholder="ឧ. ក្រសួងសេដ្ឋកិច្ច, ក្រុមហ៊ុន..."
                    v-model="createForm.sender_organization"
                    required
                  />
                </div>
              </div>

              <!-- Row 3: លេខលិខិតដើមខាងក្រៅ, កាលបរិច្ឆេទលិខិតដើម & កាលកំណត់ (Deadline) -->
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label font-weight-bold">លេខលិខិតដើមខាងក្រៅ (បើមាន)</label>
                  <input
                    type="text"
                    class="form-control"
                    placeholder="ឧ. ១២៣ សហវ.អ.ន.ប."
                    v-model="createForm.external_reference_number"
                  />
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label font-weight-bold">កាលបរិច្ឆេទលិខិតដើមខាងក្រៅ</label>
                  <input type="date" class="form-control" v-model="createForm.external_document_date" />
                </div>

                <div class="col-md-4 mb-3">
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

              <!-- Row 5: ប្រភេទឯកសារ, កម្រិតបន្ទាន់, កម្រិតសម្ងាត់ -->
              <div class="row">
                <div class="col-md-4 mb-3">
                  <label class="form-label font-weight-bold">ប្រភេទឯកសារ</label>
                  <select class="form-control" v-model="createForm.document_type">
                    <option value="LETTER">លិខិត</option>
                    <option value="PRAKAS">ប្រកាស</option>
                    <option value="DECISION">សេចក្តីសម្រេច</option>
                    <option value="REPORT">របាយការណ៍</option>
                    <option value="INVITATION">លិខិតអញ្ជើញ</option>
                    <option value="OTHER">ផ្សេងៗ</option>
                  </select>
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label font-weight-bold">កម្រិតបន្ទាន់</label>
                  <select class="form-control" v-model="createForm.urgency">
                    <option value="NORMAL">ធម្មតា</option>
                    <option value="MEDIUM">មធ្យម</option>
                    <option value="URGENT">បន្ទាន់</option>
                    <option value="MOST_URGENT">បន្ទាន់បំផុត</option>
                  </select>
                </div>

                <div class="col-md-4 mb-3">
                  <label class="form-label font-weight-bold">កម្រិតសម្ងាត់</label>
                  <select class="form-control" v-model="createForm.confidentiality">
                    <option value="NORMAL">ធម្មតា</option>
                    <option value="CONFIDENTIAL">សម្ងាត់</option>
                    <option value="TOP_SECRET">សម្ងាត់បំផុត</option>
                  </select>
                </div>
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

              <!-- Checkbox: Send immediately to DG assistant -->
              <div class="custom-control custom-checkbox mt-3 mb-2">
                <input
                  type="checkbox"
                  class="custom-control-input"
                  id="sendImmediatelyCheck"
                  v-model="createForm.send_immediately"
                />
                <label class="custom-control-label font-weight-bold text-primary" for="sendImmediatelyCheck">
                  បញ្ជូនបន្តទៅកាន់ជំនួយការអគ្គនាយកភ្លាមៗ (DG Assistant Inbox)
                </label>
              </div>

              <div class="modal-footer px-0 pb-0 pt-3 border-top">
                <button type="button" class="btn btn-secondary" @click="closeCreateModal">បោះបង់</button>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                  <i class="fas fa-save mr-1"></i> រក្សាទុក
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL 2: ជំនួយការទទួល និងចុះលេខជំនួយការអគ្គនាយក ==================== -->
    <div class="custom-modal-backdrop" v-if="showAssistantReceiveModal">
      <div class="modal-dialog modal-md">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title font-khmer font-weight-bold">
              <i class="fas fa-barcode mr-2"></i>ចុះលេខចូលជំនួយការអគ្គនាយក
            </h5>
            <button type="button" class="close text-white" @click="showAssistantReceiveModal = false">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4 font-khmer">
            <div class="alert alert-info py-2 font-13 mb-3">
              ឯកសារលេខទូទៅ៖ <strong>{{ selectedDoc?.general_inbound_number }}</strong><br />
              មកពី៖ <strong>{{ selectedDoc?.sender_organization }}</strong>
            </div>

            <form @submit.prevent="submitAssistantReceive">
              <!-- ប្រភេទប្រភពឯកសារ -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">ជ្រើសរើសប្រភេទឯកសារ / ប្រភព <span class="text-danger">*</span></label>
                <select
                  class="form-control"
                  v-model="assistantReceiveForm.dg_inbound_category"
                  @change="fetchNextDgNumber"
                  required
                >
                  <optgroup label="ឯកសារខាងក្រៅ">
                    <option value="COMPANY">ក្រុមហ៊ុន (AA001/26)</option>
                    <option value="MEF">ក្រសួងសេដ្ឋកិច្ច និងហិរញ្ញវត្ថុ (E001/26)</option>
                    <option value="FSA_REGULATOR">អ.ស.ហ. និងនិយ័តករ (NF001/26)</option>
                  </optgroup>
                  <optgroup label="ឯកសារទទួលបានពីនាយកដ្ឋានមកវិញ">
                    <option value="DEPT_GENERAL_AFFAIRS">A - នាយកដ្ឋានកិច្ចការទូទៅ (A001/26)</option>
                    <option value="DEPT_REGISTRATION">R - នាយកដ្ឋានចុះបញ្ជី (R001/26)</option>
                    <option value="DEPT_RESEARCH">T - នាយកដ្ឋានស្រាវជ្រាវ (T001/26)</option>
                    <option value="DEPT_LEGAL">L - នាយកដ្ឋានគតិយុត្ត (L001/26)</option>
                    <option value="PROJECT_ACSEP">AS - គម្រោង ACSEP (AS001/26)</option>
                  </optgroup>
                </select>
              </div>

              <!-- លេខចូលជំនួយការអគ្គនាយក (Auto-generated with edit option) -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">លេខចូលជំនួយការអគ្គនាយក <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input
                    type="text"
                    class="form-control font-weight-bold font-monospace text-primary font-16"
                    v-model="assistantReceiveForm.custom_dg_number"
                    required
                  />
                  <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" @click="fetchNextDgNumber" title="ទាញយកលេខឡើងវិញ">
                      <i class="fas fa-sync-alt"></i>
                    </button>
                  </div>
                </div>
                <small class="text-muted">ប្រព័ន្ធគណនាលេខស្វ័យប្រវត្តិតាមប្រភេទខាងលើ (អាចកែប្រែដោយដៃបានបើចាំបាច់)</small>
              </div>

              <!-- កាលបរិច្ឆេទជំនួយការចុះចូល -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">កាលបរិច្ឆេទចុះចូល <span class="text-danger">*</span></label>
                <input type="date" class="form-control" v-model="assistantReceiveForm.dg_received_date" required />
              </div>

              <!-- កំណត់សម្គាល់របស់ជំនួយការ -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">កំណត់សម្គាល់បន្ថែម (បើមាន)</label>
                <textarea class="form-control" rows="2" v-model="assistantReceiveForm.dg_assistant_notes"></textarea>
              </div>

              <div class="modal-footer px-0 pb-0 pt-3 border-top">
                <button type="button" class="btn btn-secondary" @click="showAssistantReceiveModal = false">បោះបង់</button>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                  <i class="fas fa-check mr-1"></i> ចុះលេខ & ដាក់ជូនអគ្គនាយក
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL 3: ចំណារឯកឧត្តមអគ្គនាយក (DG ANNOTATION) ==================== -->
    <div class="custom-modal-backdrop" v-if="showDgAnnotateModal">
      <div class="modal-dialog modal-md">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title font-khmer font-weight-bold">
              <i class="fas fa-pen-nib mr-2"></i>ចំណារឯកឧត្តមអគ្គនាយក
            </h5>
            <button type="button" class="close text-white" @click="showDgAnnotateModal = false">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4 font-khmer">
            <div class="alert alert-secondary py-2 font-13 mb-3">
              លេខចូល អ.ន.៖ <strong class="text-primary">{{ selectedDoc?.dg_inbound_number }}</strong><br />
              កម្មវត្ថុ៖ {{ selectedDoc?.title }}
            </div>

            <form @submit.prevent="submitDgAnnotate">
              <!-- ខ្លឹមសារចំណារ -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">ខ្លឹមសារចំណាររបស់ឯកឧត្តមអគ្គនាយក <span class="text-danger">*</span></label>
                <textarea
                  class="form-control"
                  rows="4"
                  placeholder="សូមកត់ត្រាខ្លឹមសារចំណារ..."
                  v-model="dgAnnotateForm.dg_annotation"
                  required
                ></textarea>
              </div>

              <!-- លក្ខខណ្ឌតម្រូវឱ្យឆ្លើយតប (Path A vs Path B) -->
              <div class="card bg-light border-warning p-3 mb-3">
                <label class="form-label font-weight-bold mb-2">តម្រូវការលិខិតឆ្លើយតប <span class="text-danger">*</span></label>
                <div class="custom-control custom-radio mb-2">
                  <input
                    type="radio"
                    id="radioNoResponse"
                    class="custom-control-input"
                    :value="false"
                    v-model="dgAnnotateForm.is_response_required"
                  />
                  <label class="custom-control-label" for="radioNoResponse">
                    <strong>សម្រាប់ជ្រាប / មិនបាច់ឆ្លើយតប (Path A)</strong>
                    <div class="small text-muted">អង្គភាព ឬមន្ត្រីទទួល ត្រឹមតែចុច «ទទួលជ្រាប» ដើម្បីបញ្ចប់</div>
                  </label>
                </div>

                <div class="custom-control custom-radio">
                  <input
                    type="radio"
                    id="radioRequireResponse"
                    class="custom-control-input"
                    :value="true"
                    v-model="dgAnnotateForm.is_response_required"
                  />
                  <label class="custom-control-label text-danger" for="radioRequireResponse">
                    <strong>តម្រូវឱ្យមានលិខិតឆ្លើយតប (Path B)</strong>
                    <div class="small text-muted">មន្ត្រីទទួលបន្ទុកត្រូវរៀបចំសេចក្តីព្រាងលិខិតឆ្លើយតប និងឆ្លងតាមឋានានុក្រមជូនអគ្គនាយក</div>
                  </label>
                </div>
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

              <div class="modal-footer px-0 pb-0 pt-3 border-top">
                <button type="button" class="btn btn-secondary" @click="showDgAnnotateModal = false">បោះបង់</button>
                <button type="submit" class="btn btn-danger" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                  <i class="fas fa-save mr-1"></i> រក្សាទុកចំណារ
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL 4: ជំនួយការ SCAN ចំណារ និងចែកចាយ ==================== -->
    <div class="custom-modal-backdrop" v-if="showAssistantDispatchModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title font-khmer font-weight-bold">
              <i class="fas fa-share-square mr-2"></i>Scan ចំណារអគ្គនាយក និងចែកចាយបន្ត
            </h5>
            <button type="button" class="close text-dark" @click="showAssistantDispatchModal = false">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4 font-khmer">
            <div class="alert alert-info py-2 font-13 mb-3">
              ឯកសារ៖ <strong>{{ selectedDoc?.dg_inbound_number }}</strong> |
              ចំណារ៖ <strong class="text-danger">{{ selectedDoc?.dg_annotation }}</strong> |
              ប្រភេទ៖ <strong>{{ selectedDoc?.is_response_required ? 'តម្រូវឱ្យឆ្លើយតប' : 'សម្រាប់ជ្រាប' }}</strong>
            </div>

            <form @submit.prevent="submitAssistantDispatch">
              <!-- Upload Scan ដែលមានចំណារ -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">
                  <i class="fas fa-file-upload text-primary mr-1"></i>Scan ឯកសារដែលមានចំណារអគ្គនាយក
                </label>
                <div class="custom-file">
                  <input
                    type="file"
                    class="custom-file-input"
                    id="annotatedFileInput"
                    @change="handleAnnotatedFileChange"
                    accept=".pdf,.jpg,.jpeg,.png"
                  />
                  <label class="custom-file-label" for="annotatedFileInput">
                    {{ selectedAnnotatedFileName || 'ជ្រើសរើសឯកសារ Scan ចំណារ...' }}
                  </label>
                </div>
              </div>

              <!-- គោលដៅចែកចាយ Target Type -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">កម្រិតចែកចាយ <span class="text-danger">*</span></label>
                <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                  <label class="btn btn-outline-primary font-khmer" :class="{ active: dispatchForm.target_type === 'DEPARTMENT' }">
                    <input type="radio" value="DEPARTMENT" v-model="dispatchForm.target_type" /> ថ្នាក់នាយកដ្ឋាន
                  </label>
                  <label class="btn btn-outline-primary font-khmer" :class="{ active: dispatchForm.target_type === 'OFFICE' }">
                    <input type="radio" value="OFFICE" v-model="dispatchForm.target_type" /> ថ្នាក់ការិយាល័យ
                  </label>
                  <label class="btn btn-outline-primary font-khmer" :class="{ active: dispatchForm.target_type === 'OFFICER' }">
                    <input type="radio" value="OFFICER" v-model="dispatchForm.target_type" /> មន្ត្រីទទួលបន្ទុកជាក់លាក់
                  </label>
                </div>
              </div>

              <!-- Dropdowns based on Target Type -->
              <div class="row">
                <!-- Select Department -->
                <div class="col-md-6 mb-3" v-if="dispatchForm.target_type === 'DEPARTMENT' || dispatchForm.target_type === 'OFFICE' || dispatchForm.target_type === 'OFFICER'">
                  <label class="form-label font-weight-bold">នាយកដ្ឋានគោលដៅ <span class="text-danger">*</span></label>
                  <select class="form-control" v-model="dispatchForm.target_department_id" @change="onDispatchDeptChange">
                    <option value="">-- ជ្រើសរើសនាយកដ្ឋាន --</option>
                    <option v-for="d in recipientOptions.departments" :key="d.id" :value="d.id">
                      {{ d.name_kh }}
                    </option>
                  </select>
                </div>

                <!-- Select Office -->
                <div class="col-md-6 mb-3" v-if="dispatchForm.target_type === 'OFFICE' || dispatchForm.target_type === 'OFFICER'">
                  <label class="form-label font-weight-bold">ការិយាល័យ</label>
                  <select class="form-control" v-model="dispatchForm.target_office_id">
                    <option value="">-- ជ្រើសរើសការិយាល័យ --</option>
                    <option v-for="o in filteredDispatchOffices" :key="o.id" :value="o.id">
                      {{ o.name_kh }}
                    </option>
                  </select>
                </div>

                <!-- Select Specific Officer -->
                <div class="col-md-12 mb-3" v-if="dispatchForm.target_type === 'OFFICER'">
                  <label class="form-label font-weight-bold">មន្ត្រីទទួលបន្ទុកចាត់ចែង <span class="text-danger">*</span></label>
                  <select class="form-control" v-model="dispatchForm.target_user_id" required>
                    <option value="">-- ជ្រើសរើសមន្ត្រី --</option>
                    <option v-for="u in filteredDispatchUsers" :key="u.id" :value="u.id">
                      {{ u.name_kh || u.name }} - {{ u.position?.title_kh || '' }} ({{ u.department?.name_kh || '' }})
                    </option>
                  </select>
                </div>
              </div>

              <!-- Dispatch Notes -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">សេចក្តីណែនាំ / កំណត់សម្គាល់បញ្ជូន</label>
                <textarea class="form-control" rows="2" placeholder="កំណត់សម្គាល់សម្រាប់អ្នកទទួល..." v-model="dispatchForm.dispatch_notes"></textarea>
              </div>

              <div class="modal-footer px-0 pb-0 pt-3 border-top">
                <button type="button" class="btn btn-secondary" @click="showAssistantDispatchModal = false">បោះបង់</button>
                <button type="submit" class="btn btn-warning font-weight-bold" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                  <i class="fas fa-paper-plane mr-1"></i> ចែកចាយឯកសារ
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL 5: ដាក់ស្នើព្រាងលិខិតឆ្លើយតប (PATH B DRAFT) ==================== -->
    <div class="custom-modal-backdrop" v-if="showSubmitResponseModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title font-khmer font-weight-bold">
              <i class="fas fa-reply-all mr-2"></i>រៀបចំ និងឆ្លងសេចក្តីព្រាងលិខិតឆ្លើយតប
            </h5>
            <button type="button" class="close text-dark" @click="showSubmitResponseModal = false">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4 font-khmer">
            <div class="alert alert-secondary py-2 font-13 mb-3">
              ឆ្លើយតបលើឯកសារលេខ៖ <strong>{{ selectedDoc?.dg_inbound_number }}</strong><br />
              កម្មវត្ថុដើម៖ {{ selectedDoc?.title }}<br />
              ចំណារអគ្គនាយក៖ <span class="text-danger font-weight-bold">{{ selectedDoc?.dg_annotation }}</span>
            </div>

            <form @submit.prevent="submitResponseDraftAction">
              <div class="mb-3">
                <label class="form-label font-weight-bold">កម្មវត្ថុលិខិតឆ្លើយតប <span class="text-danger">*</span></label>
                <input
                  type="text"
                  class="form-control"
                  placeholder="កម្មវត្ថុលិខិតឆ្លើយតប..."
                  v-model="responseForm.title"
                  required
                />
              </div>

              <div class="mb-3">
                <label class="form-label font-weight-bold">ខ្លឹមសារសង្ខេប / សេចក្តីរាយការណ៍</label>
                <textarea
                  class="form-control"
                  rows="4"
                  placeholder="ខ្លឹមសារសង្ខេបនៃលិខិត ឬកំណត់បង្ហាញ..."
                  v-model="responseForm.content"
                ></textarea>
              </div>

              <!-- Upload File Draft -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">
                  <i class="fas fa-file-word text-primary mr-1"></i>ភ្ជាប់ឯកសារព្រាង (Word / PDF)
                </label>
                <div class="custom-file">
                  <input
                    type="file"
                    class="custom-file-input"
                    id="responseFileInput"
                    @change="handleResponseFileChange"
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                  />
                  <label class="custom-file-label" for="responseFileInput">
                    {{ selectedResponseFileName || 'ជ្រើសរើសឯកសារព្រាង...' }}
                  </label>
                </div>
              </div>

              <!-- Next Reviewer Selection (Hierarchy Candidate) -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">
                  <i class="fas fa-user-check text-success mr-1"></i>ជ្រើសរើសថ្នាក់ដឹកនាំពិនិត្យបន្តតាមឋានានុក្រម <span class="text-danger">*</span>
                </label>
                <select class="form-control font-weight-bold text-dark" v-model="responseForm.forwarded_to_id" required>
                  <option value="">-- ជ្រើសរើសថ្នាក់ដឹកនាំ --</option>
                  <option v-for="c in responseCandidates" :key="c.id" :value="c.id">
                    {{ c.name_kh || c.name }} - {{ c.position?.title_kh || '' }}
                  </option>
                </select>
                <small class="text-muted">ប្រព័ន្ធបានចម្រាញ់បេក្ខភាពថ្នាក់ដឹកនាំដែលស្ថិតនៅក្នុងឋានានុក្រមផ្ទាល់របស់លោកអ្នក</small>
              </div>

              <div class="mb-3">
                <label class="form-label font-weight-bold">កំណត់សម្គាល់ឆ្លង</label>
                <input
                  type="text"
                  class="form-control"
                  placeholder="សូមគោរពជូន..."
                  v-model="responseForm.comment"
                />
              </div>

              <div class="modal-footer px-0 pb-0 pt-3 border-top">
                <button type="button" class="btn btn-secondary" @click="showSubmitResponseModal = false">បោះបង់</button>
                <button type="submit" class="btn btn-warning font-weight-bold" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                  <i class="fas fa-paper-plane mr-1"></i> ដាក់ស្នើឆ្លង
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL 6: ថ្នាក់ដឹកនាំពិនិត្យ & ចារឆ្លងលិខិតឆ្លើយតប (REVIEW ACTION) ==================== -->
    <div class="custom-modal-backdrop" v-if="showReviewResponseModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-dark-custom text-white">
            <h5 class="modal-title font-khmer font-weight-bold">
              <i class="fas fa-clipboard-check text-warning mr-2"></i>ពិនិត្យ និងចារឆ្លងលិខិតឆ្លើយតប
            </h5>
            <button type="button" class="close text-white" @click="showReviewResponseModal = false">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4 font-khmer">
            <div class="alert alert-secondary py-2 font-13 mb-3">
              ឯកសារដើមលេខ៖ <strong>{{ selectedDoc?.dg_inbound_number }}</strong><br />
              លិខិតឆ្លើយតប៖ <strong>{{ selectedResponse?.title }}</strong><br />
              អ្នករៀបចំ៖ <strong>{{ selectedResponse?.drafted_by_user?.name_kh }}</strong>
              <div class="mt-2" v-if="selectedResponse?.file_path">
                <a :href="getResponseFileUrl(selectedResponse.id)" target="_blank" class="btn btn-xs btn-outline-primary">
                  <i class="fas fa-download mr-1"></i> ទាញយកឯកសារព្រាងពិនិត្យ
                </a>
              </div>
            </div>

            <form @submit.prevent="submitReviewAction">
              <!-- Action Selection -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">សកម្មភាពសម្រេច <span class="text-danger">*</span></label>
                <div class="btn-group btn-group-toggle w-100">
                  <label class="btn btn-outline-primary font-khmer" :class="{ active: reviewActionForm.action === 'FORWARD' }" v-if="!isDg">
                    <input type="radio" value="FORWARD" v-model="reviewActionForm.action" />
                    <i class="fas fa-share mr-1"></i> បញ្ជូនបន្តឡើងលើ
                  </label>
                  <label class="btn btn-outline-danger font-khmer" :class="{ active: reviewActionForm.action === 'RETURN' }">
                    <input type="radio" value="RETURN" v-model="reviewActionForm.action" />
                    <i class="fas fa-undo mr-1"></i> បញ្ជូនត្រឡប់កែសម្រួល
                  </label>
                  <label class="btn btn-outline-success font-khmer font-weight-bold" :class="{ active: reviewActionForm.action === 'DG_APPROVE' }" v-if="isDg || isAdmin">
                    <input type="radio" value="DG_APPROVE" v-model="reviewActionForm.action" />
                    <i class="fas fa-signature mr-1"></i> ឯកភាព & ចុះហត្ថលេខា (អគ្គនាយក)
                  </label>
                </div>
              </div>

              <!-- If Action is FORWARD: select next reviewer -->
              <div class="mb-3" v-if="reviewActionForm.action === 'FORWARD'">
                <label class="form-label font-weight-bold">ជ្រើសរើសថ្នាក់ដឹកនាំបន្ទាប់ <span class="text-danger">*</span></label>
                <select class="form-control font-weight-bold" v-model="reviewActionForm.forwarded_to_id" required>
                  <option value="">-- ជ្រើសរើសថ្នាក់ដឹកនាំ --</option>
                  <option v-for="c in responseCandidates" :key="c.id" :value="c.id">
                    {{ c.name_kh || c.name }} - {{ c.position?.title_kh || '' }}
                  </option>
                </select>
              </div>

              <!-- If Action is DG_APPROVE: optional response number -->
              <div class="mb-3" v-if="reviewActionForm.action === 'DG_APPROVE'">
                <label class="form-label font-weight-bold">លេខលិខិតចេញជាផ្លូវការ (បើមាន)</label>
                <input
                  type="text"
                  class="form-control font-weight-bold font-monospace"
                  placeholder="ឧ. ៤៥៦ ន.ប.ធ."
                  v-model="reviewActionForm.response_number"
                />
              </div>

              <!-- Review Comment / Annotation -->
              <div class="mb-3">
                <label class="form-label font-weight-bold">ចំណារ / មតិយោបល់ណែនាំ</label>
                <textarea
                  class="form-control"
                  rows="3"
                  placeholder="មតិយោបល់ ឬការកែសម្រួល..."
                  v-model="reviewActionForm.comment"
                ></textarea>
              </div>

              <div class="modal-footer px-0 pb-0 pt-3 border-top">
                <button type="button" class="btn btn-secondary" @click="showReviewResponseModal = false">បោះបង់</button>
                <button type="submit" class="btn btn-primary" :disabled="submitting">
                  <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                  <i class="fas fa-check mr-1"></i> អនុវត្តសកម្មភាព
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- ==================== MODAL 7: មើលព័ត៌មានលម្អិត & ប្រវត្តិលំហូរ (DETAIL & AUDIT) ==================== -->
    <div class="custom-modal-backdrop" v-if="showDetailModal">
      <div class="modal-dialog modal-xl">
        <div class="modal-content shadow-lg border-0">
          <div class="modal-header bg-dark-custom text-white">
            <h5 class="modal-title font-khmer font-weight-bold">
              <i class="fas fa-info-circle text-info mr-2"></i>ព័ត៌មានលម្អិត និងប្រវត្តិលំហូរឯកសារ
            </h5>
            <button type="button" class="close text-white" @click="showDetailModal = false">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body p-4 font-khmer" v-if="selectedDoc">
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
                          <td class="text-muted" style="width: 170px;">លេខចូលទូទៅ៖</td>
                          <td><span class="badge badge-dark font-13">{{ selectedDoc.general_inbound_number || '---' }}</span></td>
                        </tr>
                        <tr>
                          <td class="text-muted">លេខចូល អ.ន.៖</td>
                          <td>
                            <span v-if="selectedDoc.dg_inbound_number" class="badge badge-primary font-13">
                              {{ selectedDoc.dg_inbound_number }}
                            </span>
                            <span v-else class="text-muted small">មិនទាន់ចុះ</span>
                          </td>
                        </tr>
                        <tr>
                          <td class="text-muted">កាលបរិច្ឆេទចូល៖</td>
                          <td><strong>{{ formatDateKh(selectedDoc.received_date) }}</strong> ម៉ោង {{ selectedDoc.received_time }}</td>
                        </tr>
                        <tr>
                          <td class="text-muted">អ្នកយកមក៖</td>
                          <td><strong>{{ selectedDoc.deliverer_name }}</strong> {{ selectedDoc.deliverer_phone ? `(${selectedDoc.deliverer_phone})` : '' }}</td>
                        </tr>
                        <tr>
                          <td class="text-muted">មកពីអង្គភាព៖</td>
                          <td><strong class="text-dark font-14">{{ selectedDoc.sender_organization }}</strong></td>
                        </tr>
                        <tr>
                          <td class="text-muted">លេខលិខិតដើម៖</td>
                          <td>{{ selectedDoc.external_reference_number || '---' }} {{ selectedDoc.external_document_date ? `(${formatDateKh(selectedDoc.external_document_date)})` : '' }}</td>
                        </tr>
                        <tr>
                          <td class="text-muted">កម្មវត្ថុ៖</td>
                          <td><span class="font-weight-bold text-primary font-14">{{ selectedDoc.title }}</span></td>
                        </tr>
                        <tr>
                          <td class="text-muted">ប្រភេទ / បន្ទាន់ / សម្ងាត់៖</td>
                          <td>
                            <span class="badge badge-light border mr-1">{{ selectedDoc.document_type_kh }}</span>
                            <span class="badge badge-warning text-dark mr-1">{{ selectedDoc.urgency_kh }}</span>
                            <span class="badge badge-dark">{{ selectedDoc.confidentiality_kh }}</span>
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
                            <span class="badge px-2 py-1 font-12" :class="getStatusBadgeClass(selectedDoc.status)">
                              {{ selectedDoc.status_kh }}
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
                        <a
                          v-if="selectedDoc.original_file_path"
                          :href="getOriginalFileUrl(selectedDoc.id)"
                          target="_blank"
                          class="btn btn-sm btn-outline-primary"
                        >
                          <i class="fas fa-file-pdf text-danger mr-1"></i> Scan ឯកសារដើម ({{ selectedDoc.original_file_name || 'ទាញយក' }})
                        </a>

                        <!-- Annotated Scan -->
                        <a
                          v-if="selectedDoc.annotated_file_path"
                          :href="getAnnotatedFileUrl(selectedDoc.id)"
                          target="_blank"
                          class="btn btn-sm btn-outline-danger"
                        >
                          <i class="fas fa-signature mr-1"></i> Scan ចំណារអគ្គនាយក ({{ selectedDoc.annotated_file_name || 'ទាញយក' }})
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column: Flow Movements & Response History -->
              <div class="col-lg-5 mb-3">
                <div class="card border rounded-lg h-100">
                  <div class="card-header bg-light py-2 font-weight-bold text-dark">
                    <i class="fas fa-history text-secondary mr-1"></i> ប្រវត្តិនៃការចរាចរឯកសារ
                  </div>
                  <div class="card-body p-3 overflow-auto max-h-500 font-13">
                    <!-- Timeline Item list -->
                    <div class="timeline" v-if="selectedDoc.movements && selectedDoc.movements.length > 0">
                      <div v-for="m in selectedDoc.movements" :key="m.id" class="mb-3 pl-3 border-left border-primary position-relative timeline-step">
                        <div class="font-weight-bold text-dark">
                          {{ m.action_kh }}
                        </div>
                        <div class="small text-muted mb-1">
                          <i class="far fa-user mr-1"></i>{{ m.user?.name_kh || m.user?.name }} |
                          <i class="far fa-clock mr-1"></i>{{ formatDateTimeKh(m.created_at) }}
                        </div>
                        <div class="text-secondary font-12" v-if="m.comment">
                          {{ m.comment }}
                        </div>
                      </div>
                    </div>
                    <div v-else class="text-muted text-center py-4">
                      មិនទាន់មានប្រវត្តិផ្លាស់ប្តូរឡើយ
                    </div>

                    <!-- Latest Response Info if any -->
                    <div class="mt-4 pt-3 border-top" v-if="selectedDoc.responses && selectedDoc.responses.length > 0">
                      <span class="font-weight-bold text-dark d-block mb-2">
                        <i class="fas fa-reply-all text-warning mr-1"></i> សេចក្តីព្រាងលិខិតឆ្លើយតប៖
                      </span>
                      <div class="card bg-light p-2 font-12 mb-2" v-for="resp in selectedDoc.responses" :key="resp.id">
                        <div class="font-weight-bold text-primary">{{ resp.title }}</div>
                        <div class="text-muted">ស្ថានភាព៖ <span class="badge badge-info">{{ resp.status_kh }}</span> ({{ resp.stage_kh }})</div>
                        <div class="text-muted">រៀបចំដោយ៖ {{ resp.drafted_by_user?.name_kh }} | {{ formatDateTimeKh(resp.created_at) }}</div>
                        <div class="mt-1" v-if="resp.file_path">
                          <a :href="getResponseFileUrl(resp.id)" target="_blank" class="text-primary font-weight-bold">
                            <i class="fas fa-download mr-1"></i> ទាញយកឯកសារព្រាង
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer font-khmer justify-content-between">
            <div>
              <router-link
                v-if="selectedDoc"
                :to="`/inbound-documents/${selectedDoc.id}/routing-slip`"
                target="_blank"
                class="btn btn-outline-primary"
              >
                <i class="fas fa-print mr-1"></i> បោះពុម្ពសន្លឹកតាមដាន (Routing Slip & QR)
              </router-link>
            </div>
            <div>
              <button type="button" class="btn btn-secondary" @click="showDetailModal = false">បិទ</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 8. Modal ចាត់ចែង និងបញ្ជូនបន្តតាមឋានានុក្រម (Cascading Forward Modal) -->
    <div
      class="modal fade show d-block font-khmer"
      tabindex="-1"
      v-if="showForwardModal && selectedDoc"
      style="background: rgba(0, 0, 0, 0.55);"
    >
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-lg">
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
                    <span class="text-muted small d-block">លេខចូលទូទៅ</span>
                    <span class="font-weight-bold text-dark font-15">{{ selectedDoc.general_inbound_number }}</span>
                  </div>
                  <div class="col-sm-6 mb-2" v-if="selectedDoc.dg_inbound_number">
                    <span class="text-muted small d-block">លេខចូលជំនួយការអគ្គនាយក</span>
                    <span class="font-weight-bold text-primary font-15">{{ selectedDoc.dg_inbound_number }}</span>
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
                  <div class="custom-control custom-radio mr-4">
                    <input
                      type="radio"
                      id="fwdTargetDept"
                      name="fwd_target_type"
                      class="custom-control-input"
                      value="DEPARTMENT"
                      v-model="forwardForm.target_type"
                    />
                    <label class="custom-control-label cursor-pointer" for="fwdTargetDept">
                      <i class="fas fa-building text-secondary mr-1"></i>នាយកដ្ឋាន
                    </label>
                  </div>
                  <div class="custom-control custom-radio mr-4">
                    <input
                      type="radio"
                      id="fwdTargetOffice"
                      name="fwd_target_type"
                      class="custom-control-input"
                      value="OFFICE"
                      v-model="forwardForm.target_type"
                    />
                    <label class="custom-control-label cursor-pointer" for="fwdTargetOffice">
                      <i class="fas fa-door-closed text-info mr-1"></i>ការិយាល័យ
                    </label>
                  </div>
                  <div class="custom-control custom-radio">
                    <input
                      type="radio"
                      id="fwdTargetOfficer"
                      name="fwd_target_type"
                      class="custom-control-input"
                      value="OFFICER"
                      v-model="forwardForm.target_type"
                    />
                    <label class="custom-control-label cursor-pointer" for="fwdTargetOfficer">
                      <i class="fas fa-user-circle text-primary mr-1"></i>មន្ត្រីជាក់លាក់
                    </label>
                  </div>
                </div>
              </div>

              <!-- Department Dropdown -->
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
                  <option v-for="o in recipientOptions.offices" :key="o.id" :value="o.id">
                    {{ o.name_kh }} ({{ o.department?.name_kh }})
                  </option>
                </select>
              </div>

              <!-- Officer Dropdown -->
              <div class="form-group mb-3" v-if="forwardForm.target_type === 'OFFICER'">
                <label class="font-weight-bold text-dark">មន្ត្រីទទួលបន្ទុក <span class="text-danger">*</span></label>
                <select class="form-control" v-model="forwardForm.target_user_id" required>
                  <option value="" disabled>-- សូមជ្រើសរើសមន្ត្រី --</option>
                  <option v-for="u in recipientOptions.users" :key="u.id" :value="u.id">
                    {{ u.name_kh || u.name }} - {{ u.position?.name_kh || 'មន្ត្រី' }} ({{ u.department?.name_kh }})
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

    <!-- 9. Modal កំណត់ និងតេស្តការជូនដំណឹងតាម Telegram Bot -->
    <div
      class="modal fade show d-block font-khmer"
      tabindex="-1"
      v-if="showTelegramModal"
      style="background: rgba(0, 0, 0, 0.55);"
    >
      <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-lg">
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

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import { useUserStore } from '@/stores/user';
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
  apiDeleteInboundDocument,
  apiUpdateTelegramSettings,
  apiTestTelegramConnection,
  getOriginalDownloadUrl,
  getAnnotatedDownloadUrl,
  getResponseDownloadUrl
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
  return userStore.position && Number(userStore.position.level) === 1;
});
const isRegularOfficer = computed(() => {
  return !isAdmin.value && !canRegister.value && !canAssist.value && !isDg.value;
});

// --- State ---
const loading = ref(false);
const submitting = ref(false);
const activeTab = ref('all');

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
  reception_pending: 0,
  assistant_pending: 0,
  dg_pending: 0,
  dispatched: 0,
  in_response: 0,
  completed: 0,
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

// Modals display states
const showCreateModal = ref(false);
const showAssistantReceiveModal = ref(false);
const showDgAnnotateModal = ref(false);
const showAssistantDispatchModal = ref(false);
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

const dgAnnotateForm = reactive({
  dg_annotation: '',
  is_response_required: false,
  deadline: '',
});

const dispatchForm = reactive({
  target_type: 'DEPARTMENT',
  target_department_id: '',
  target_office_id: '',
  target_user_id: '',
  dispatch_notes: '',
});

const responseForm = reactive({
  title: '',
  content: '',
  forwarded_to_id: '',
  comment: '',
});

const reviewActionForm = reactive({
  action: 'FORWARD',
  forwarded_to_id: '',
  response_number: '',
  comment: '',
});

const forwardForm = reactive({
  target_type: 'DEPARTMENT',
  target_department_id: '',
  target_office_id: '',
  target_user_id: '',
  forwarding_notes: '',
});

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
const canAcknowledge = (doc) => {
  if (doc.status !== 'DISPATCHED' || doc.is_response_required) return false;
  // If target user is viewer, or target office/dept is viewer's office/dept
  return isAdmin.value ||
    doc.target_user_id === userStore.id ||
    (doc.target_office_id && doc.target_office_id === userStore.office_id) ||
    (doc.target_department_id && doc.target_department_id === userStore.department_id);
};

const canDraftResponse = (doc) => {
  if (!doc.is_response_required) return false;
  if (doc.status !== 'DISPATCHED' && doc.status !== 'IN_RESPONSE_PROGRESS') return false;
  // If no responses drafted yet, or returned to viewer
  const latest = doc.latest_response;
  if (!latest) {
    return isAdmin.value ||
      doc.target_user_id === userStore.id ||
      (doc.target_office_id && doc.target_office_id === userStore.office_id) ||
      (doc.target_department_id && doc.target_department_id === userStore.department_id);
  }
  return latest.status === 'RETURNED' && (latest.drafted_by === userStore.id || isAdmin.value);
};

const canReviewResponse = (doc) => {
  const latest = doc.latest_response;
  if (!latest || latest.status !== 'UNDER_REVIEW') return false;
  return isAdmin.value || latest.current_approver_id === userStore.id;
};

const canForward = (doc) => {
  if (!doc) return false;
  // Do not allow forward if not yet dispatched or already completed/cancelled
  if (['COMPLETED', 'CANCELLED', 'RECEPTION_DRAFT', 'SUBMITTED_TO_ASSISTANT', 'SUBMITTED_TO_DG', 'DG_ANNOTATED'].includes(doc.status)) return false;
  if (isAdmin.value) return true;
  // DG or Deputy DG (level 1 or 2)
  if (userStore.position && Number(userStore.position.level) <= 2) return true;
  // Assigned officer
  if (doc.target_user_id && doc.target_user_id === userStore.id) return true;
  // Assigned department head or department member
  if (doc.target_department_id && doc.target_department_id === userStore.department_id) return true;
  // Assigned office head or office member
  if (doc.target_office_id && doc.target_office_id === userStore.office_id) return true;
  // Assistant
  if (canAssist.value) return true;
  return false;
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
  if (t && ['all', 'my_todo', 'my_unit', 'my_done', 'reception', 'assistant_inbox', 'dg_inbox', 'assigned_to_me', 'response_workflow'].includes(t)) {
    activeTab.value = t;
  } else {
    activeTab.value = isRegularOfficer.value ? 'my_todo' : 'all';
  }
};

const switchTab = (tab) => {
  activeTab.value = tab;
  filters.page = 1;
  const defaultTab = isRegularOfficer.value ? 'my_todo' : 'all';
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
    filters.page = 1;
    loadDocuments();
  }
);

const toggleDeadlineFilter = (type) => {
  if (filters.deadline_filter === type) {
    filters.deadline_filter = '';
  } else {
    filters.deadline_filter = type;
  }
  filters.page = 1;
  loadDocuments();
};

const toggleStatusFilter = (statusVal) => {
  if (filters.status === statusVal) {
    filters.status = '';
  } else {
    filters.status = statusVal;
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
  showCreateModal.value = true;
};

const closeCreateModal = () => {
  showCreateModal.value = false;
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

const openDgAnnotateModal = (doc) => {
  selectedDoc.value = doc;
  dgAnnotateForm.dg_annotation = doc.dg_annotation || '';
  dgAnnotateForm.is_response_required = !!doc.is_response_required;
  dgAnnotateForm.deadline = doc.deadline ? String(doc.deadline).substring(0, 10) : '';
  showDgAnnotateModal.value = true;
};

const submitDgAnnotate = async () => {
  submitting.value = true;
  try {
    await apiDgAnnotate(selectedDoc.value.id, dgAnnotateForm);
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានកត់ត្រាចំណារឯកឧត្តមអគ្គនាយករួចរាល់!',
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

const openAssistantDispatchModal = (doc) => {
  selectedDoc.value = doc;
  selectedAnnotatedFile.value = null;
  selectedAnnotatedFileName.value = '';
  dispatchForm.target_type = 'DEPARTMENT';
  dispatchForm.target_department_id = '';
  dispatchForm.target_office_id = '';
  dispatchForm.target_user_id = '';
  dispatchForm.dispatch_notes = '';
  showAssistantDispatchModal.value = true;
};

const submitAssistantDispatch = async () => {
  submitting.value = true;
  try {
    const formData = new FormData();
    formData.append('target_type', dispatchForm.target_type);
    if (dispatchForm.target_department_id) formData.append('target_department_id', dispatchForm.target_department_id);
    if (dispatchForm.target_office_id) formData.append('target_office_id', dispatchForm.target_office_id);
    if (dispatchForm.target_user_id) formData.append('target_user_id', dispatchForm.target_user_id);
    if (dispatchForm.dispatch_notes) formData.append('dispatch_notes', dispatchForm.dispatch_notes);
    if (selectedAnnotatedFile.value) formData.append('annotated_file', selectedAnnotatedFile.value);

    await apiAssistantDispatch(selectedDoc.value.id, formData);
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បាន Scan និងចែកចាយឯកសារទៅកាន់ភាគីពាក់ព័ន្ធរួចរាល់!',
      confirmButtonText: 'យល់ព្រម',
    });
    showAssistantDispatchModal.value = false;
    loadDocuments();
    loadStats();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចចែកចាយឯកសារបានឡើយ!' });
  } finally {
    submitting.value = false;
  }
};

const openForwardModal = (doc) => {
  selectedDoc.value = doc;
  forwardForm.target_type = doc.target_type || 'DEPARTMENT';
  forwardForm.target_department_id = doc.target_department_id || '';
  forwardForm.target_office_id = doc.target_office_id || '';
  forwardForm.target_user_id = doc.target_user_id || '';
  forwardForm.forwarding_notes = '';
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
  if (forwardForm.target_type === 'OFFICER' && !forwardForm.target_user_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសមន្ត្រីទទួលបន្ទុក!' });
    return;
  }

  forwarding.value = true;
  try {
    const payload = {
      target_type: forwardForm.target_type,
      target_department_id: forwardForm.target_type === 'DEPARTMENT' ? forwardForm.target_department_id : null,
      target_office_id: forwardForm.target_type === 'OFFICE' ? forwardForm.target_office_id : null,
      target_user_id: forwardForm.target_type === 'OFFICER' ? forwardForm.target_user_id : null,
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
    Swal.fire({
      icon: 'error',
      title: 'បរាជ័យ!',
      text: err.response?.data?.message || 'មិនអាចបញ្ជូនបន្តបានឡើយ!',
    });
  } finally {
    forwarding.value = false;
  }
};

const acknowledgeAction = async (doc) => {
  const confirm = await Swal.fire({
    title: 'ទទួលជ្រាបឯកសារ?',
    text: `តើលោកអ្នកបានជ្រាប និងបញ្ចប់ដំណើរការឯកសារ ${doc.dg_inbound_number || doc.general_inbound_number} មែនទេ?`,
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'ទទួលជ្រាប & បញ្ចប់',
    cancelButtonText: 'បោះបង់',
  });
  if (!confirm.isConfirmed) return;

  try {
    await apiAcknowledgeInboundDocument(doc.id);
    Swal.fire({ icon: 'success', title: 'ជោគជ័យ!', text: 'បានទទួលជ្រាប និងបញ្ចប់ឯកសារដោយជោគជ័យ!', timer: 1500 });
    loadDocuments();
    loadStats();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចទទួលជ្រាបបានឡើយ!' });
  }
};

const openSubmitResponseModal = async (doc) => {
  selectedDoc.value = doc;
  responseForm.title = `លិខិតឆ្លើយតបលើ៖ ${doc.title}`;
  responseForm.content = '';
  responseForm.forwarded_to_id = '';
  responseForm.comment = '';
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

const submitResponseDraftAction = async () => {
  submitting.value = true;
  try {
    const formData = new FormData();
    formData.append('title', responseForm.title);
    formData.append('content', responseForm.content || '');
    formData.append('forwarded_to_id', responseForm.forwarded_to_id);
    formData.append('comment', responseForm.comment || '');
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
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចដាក់ស្នើឆ្លើយតបបានឡើយ!' });
  } finally {
    submitting.value = false;
  }
};

const openReviewResponseModal = async (doc) => {
  selectedDoc.value = doc;
  selectedResponse.value = doc.latest_response;
  reviewActionForm.action = isDg.value ? 'DG_APPROVE' : 'FORWARD';
  reviewActionForm.forwarded_to_id = '';
  reviewActionForm.response_number = '';
  reviewActionForm.comment = '';

  try {
    const res = await apiGetNextResponseApprovers(doc.id);
    responseCandidates.value = res.data.candidates || [];
    showReviewResponseModal.value = true;
  } catch (err) {
    console.error('Failed to load candidate approvers:', err);
  }
};

const submitReviewAction = async () => {
  submitting.value = true;
  try {
    const formData = new FormData();
    formData.append('action', reviewActionForm.action);
    if (reviewActionForm.comment) formData.append('comment', reviewActionForm.comment);
    if (reviewActionForm.forwarded_to_id) formData.append('forwarded_to_id', reviewActionForm.forwarded_to_id);
    if (reviewActionForm.response_number) formData.append('response_number', reviewActionForm.response_number);

    await apiProcessResponseAction(selectedDoc.value.id, formData);
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: 'បានអនុវត្តសកម្មភាពពិនិត្យលិខិតឆ្លើយតបដោយជោគជ័យ!',
      confirmButtonText: 'យល់ព្រម',
    });
    showReviewResponseModal.value = false;
    loadDocuments();
    loadStats();
  } catch (err) {
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: err.response?.data?.message || 'មិនអាចដំណើរការសកម្មភាពបានឡើយ!' });
  } finally {
    submitting.value = false;
  }
};

const openDetailModal = async (doc) => {
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
  const confirm = await Swal.fire({
    title: 'លុបឯកសារចូល?',
    text: `តើលោកអ្នកពិតជាចង់លុបឯកសារលេខ ${doc.general_inbound_number} មែនទេ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានឡើយ!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'លុបចេញ',
    cancelButtonText: 'បោះបង់',
    confirmButtonColor: '#dc3545',
  });
  if (!confirm.isConfirmed) return;

  try {
    await apiDeleteInboundDocument(doc.id);
    Swal.fire({ icon: 'success', title: 'បានលុប!', timer: 1500, showConfirmButton: false });
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

// --- Download File Helpers ---
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
  padding: 1.5rem 1rem;
}

.modal-dialog {
  margin: auto;
  max-height: 90vh;
}

.modal-content {
  border-radius: 8px;
  max-height: 88vh;
  overflow-y: auto;
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
</style>

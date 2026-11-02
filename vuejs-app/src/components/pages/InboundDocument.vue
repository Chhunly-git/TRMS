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
                    {{ isRegularOfficer ? 'ឯកសារទទួលបានសរុប' : 'ឯកសារចូលសរុប' }}
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
                  <span class="text-muted small d-block font-weight-bold">Pending (ករណីចូលមកដំបូង)</span>
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
                  <span class="text-muted small d-block font-weight-bold">Doing (បញ្ជូនចេញ / កំពុងធ្វើ)</span>
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
                  <span class="text-muted small d-block font-weight-bold">Complete (អគ្គនាយកឯកភាព)</span>
                  <h3 class="font-weight-bold mb-0 text-success">{{ stats.completed || 0 }}</h3>
                </div>
                <div class="stat-icon-circle bg-success-light text-success">
                  <i class="fas fa-check-circle fa-lg"></i>
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

          <!-- Card 8: អង្គភាពរបស់ខ្ញុំ (My Unit) -->
          <div class="col-xl col-md-4 col-sm-6 mb-2" v-if="userStore.department_id || userStore.office_id">
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
            <ul class="nav nav-tabs border-0 px-3 pt-2 align-items-center">
              <!-- Tab 1: ឯកសារទាំងអស់ (All) / ឯកសារទទួលបានទាំងអស់ -->
              <li class="nav-item">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'all' }"
                  href="javascript:void(0)"
                  @click="switchTab('all')"
                >
                  <i class="fas fa-th-list text-secondary mr-1"></i> {{ isRegularOfficer ? 'ឯកសារទទួលបានទាំងអស់' : 'ឯកសារទាំងអស់' }}
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
                  <i class="fas fa-clock text-warning mr-1"></i> Pending (ចូលដំបូង)
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
                  <i class="fas fa-tasks text-primary mr-1"></i> Doing (បញ្ជូនចេញ / កំពុងធ្វើ)
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
                  <i class="fas fa-check-circle text-success mr-1"></i> Complete (អគ្គនាយកឯកភាព)
                  <span class="badge badge-success ml-1" v-if="stats.completed > 0">{{ stats.completed }}</span>
                </a>
              </li>

              <!-- Divider if specialized desks exist -->
              <li class="nav-item d-none d-lg-block mx-2 text-muted" v-if="userStore.department_id || canRegister || canAssist || isDg || isAdmin" style="opacity: 0.35;">
                |
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

              <!-- Tab 6: តុចុះបញ្ជីទទួល (Reception Desk) -->
              <li class="nav-item" v-if="canRegister">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'reception' }"
                  href="javascript:void(0)"
                  @click="switchTab('reception')"
                >
                  <i class="fas fa-inbox mr-1 text-info"></i> តុចុះបញ្ជីទទួល
                  <span class="badge badge-info ml-1" v-if="stats.reception_pending > 0">{{ stats.reception_pending }}</span>
                </a>
              </li>

              <!-- Tab 7: តុជំនួយការ (Assistant Desk) -->
              <li class="nav-item" v-if="canAssist">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'assistant_inbox' }"
                  href="javascript:void(0)"
                  @click="switchTab('assistant_inbox')"
                >
                  <i class="fas fa-user-shield mr-1 text-indigo"></i> តុជំនួយការ
                  <span class="badge badge-indigo text-white ml-1" v-if="stats.assistant_pending > 0">{{ stats.assistant_pending }}</span>
                </a>
              </li>

              <!-- Tab 8: តុអគ្គនាយក (DG Desk) -->
              <li class="nav-item" v-if="isDg || isAdmin">
                <a
                  class="nav-link font-weight-bold"
                  :class="{ active: activeTab === 'dg_inbox' }"
                  href="javascript:void(0)"
                  @click="switchTab('dg_inbox')"
                >
                  <i class="fas fa-pen-nib mr-1 text-rose"></i> តុអគ្គនាយក
                  <span class="badge badge-rose text-white ml-1" v-if="stats.dg_pending > 0">{{ stats.dg_pending }}</span>
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
                  <option value="pending">🟡 Pending (ករណីចូលដំបូង)</option>
                  <option value="doing">🔵 Doing (បញ្ជូនចេញ/កំពុងធ្វើ)</option>
                  <option value="completed">🟢 Complete (អគ្គនាយកឯកភាព)</option>
                </select>
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
                    <th style="min-width: 175px;">ទីតាំងឯកសារ / អ្នកកាន់បច្ចុប្បន្ន</th>
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

                    <!-- 6. ស្ថានភាព (Pending, Doing, Complete) -->
                    <td>
                      <div class="d-flex align-items-center mb-1">
                        <span class="badge px-2 py-1 font-12 d-inline-flex align-items-center shadow-2xs" :class="getSimplifiedStatus(doc).badgeClass">
                          <i :class="getSimplifiedStatus(doc).icon" class="mr-1"></i>
                          <span>{{ getSimplifiedStatus(doc).label }}</span>
                        </span>
                      </div>
                      <div class="small text-muted font-11" v-if="getSimplifiedStatus(doc).subText">
                        {{ getSimplifiedStatus(doc).subText }}
                      </div>
                    </td>

                    <!-- 7. ទីតាំងឯកសារ / អ្នកកាន់បច្ចុប្បន្ន (Current Custodian & Desk) -->
                    <td>
                      <div class="d-flex align-items-center mb-1">
                        <span class="badge px-2 py-1 font-12 d-inline-flex align-items-center text-wrap" :class="getCurrentCustodian(doc).badgeClass">
                          <i :class="getCurrentCustodian(doc).icon" class="mr-1"></i>{{ getCurrentCustodian(doc).label }}
                        </span>
                      </div>
                      <div class="small text-muted" v-if="getCurrentCustodian(doc).subText">
                        {{ getCurrentCustodian(doc).subText }}
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
                          v-if="(canAssist || isDg || isAdmin) && doc.status === 'DG_ANNOTATED'"
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
                          autofocus
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
                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">លេខលិខិតដើម (ខាងក្រៅ)</label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="ឧ. ១២៣ សជណ"
                      v-model="editForm.external_reference_number"
                    />
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">កាលបរិច្ឆេទលិខិតដើម</label>
                    <input type="date" class="form-control" v-model="editForm.external_document_date" />
                  </div>

                  <div class="col-md-4 mb-3">
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

                <!-- Row 5: ប្រភេទឯកសារ, កម្រិតបន្ទាន់, កម្រិតសម្ងាត់ -->
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">ប្រភេទឯកសារ</label>
                    <select class="form-control" v-model="editForm.document_type">
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
                    <select class="form-control" v-model="editForm.urgency">
                      <option value="NORMAL">ធម្មតា</option>
                      <option value="MEDIUM">មធ្យម</option>
                      <option value="URGENT">បន្ទាន់</option>
                      <option value="MOST_URGENT">បន្ទាន់បំផុត</option>
                    </select>
                  </div>

                  <div class="col-md-4 mb-3">
                    <label class="form-label font-weight-bold">កម្រិតសម្ងាត់</label>
                    <select class="form-control" v-model="editForm.confidentiality">
                      <option value="NORMAL">ធម្មតា</option>
                      <option value="CONFIDENTIAL">សម្ងាត់</option>
                      <option value="TOP_SECRET">សម្ងាត់បំផុត</option>
                    </select>
                  </div>
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
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showAssistantReceiveModal">
        <div class="modal-dialog modal-md modal-dialog-centered my-auto" style="width: 100%; max-width: 600px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4 flex-shrink-0 align-items-center">
              <h5 class="modal-title font-khmer font-weight-bold mb-0">
                <i class="fas fa-barcode mr-2"></i>ចុះលេខចូលជំនួយការអគ្គនាយក
              </h5>
              <button type="button" class="close text-white" @click="showAssistantReceiveModal = false">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body p-4 font-khmer" style="max-height: calc(85vh - 120px); overflow-y: auto;">
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
                លេខចូល អ.ន.៖ <strong class="text-primary">{{ selectedDoc?.dg_inbound_number }}</strong><br />
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

                <!-- ជ្រើសរើសភាគីពាក់ព័ន្ធដើម្បីចាត់ចែងភ្លាមៗ (នាយកដ្ឋាន, ការិយាល័យ, មន្ត្រី, ក្រុមការងារ) -->
                <div class="mb-3">
                  <div class="card border rounded-lg p-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <label class="form-label font-weight-bold text-dark m-0">
                        <i class="fas fa-sitemap text-danger mr-1"></i> ចំណារចាត់ចែងទៅកាន់ភាគីពាក់ព័ន្ធ (ជ្រើសរើសដើម្បីចាត់ចែងភ្លាមៗ)
                      </label>
                      <span class="badge badge-dark">
                        សរុប {{ dgAnnotateForm.department_ids.length + dgAnnotateForm.office_ids.length + dgAnnotateForm.user_ids.length + (dgAnnotateForm.working_group_ids ? dgAnnotateForm.working_group_ids.length : 0) }} គោលដៅ
                      </span>
                    </div>
                    <small class="text-muted d-block mb-3">
                      * ឯកឧត្តមអគ្គនាយកអាចជ្រើសរើសគោលដៅចាត់ចែងភ្លាមៗ ឬទុកឱ្យជំនួយការ Scan និងចែកចាយតាមក្រោយ។
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

    <!-- ==================== MODAL 4: ជំនួយការ SCAN ចំណារ និងចែកចាយ ==================== -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showAssistantDispatchModal">
        <div class="modal-dialog modal-lg modal-dialog-centered my-auto" style="width: 100%; max-width: 800px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden">
            <div class="modal-header bg-warning text-dark py-3 px-4 flex-shrink-0 align-items-center">
              <h5 class="modal-title font-khmer font-weight-bold mb-0">
                <i class="fas fa-share-square mr-2"></i>Scan ចំណារអគ្គនាយក និងចែកចាយបន្ត
              </h5>
              <button type="button" class="close text-dark" @click="showAssistantDispatchModal = false">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body p-4 font-khmer" style="max-height: calc(85vh - 120px); overflow-y: auto;">
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

                <!-- ចាត់ចែងច្រើនគោលដៅ Multi-Target Dispatch (នាយកដ្ឋាន, ការិយាល័យ, មន្ត្រី) -->
                <div class="mb-3">
                  <div class="card border rounded-lg p-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <label class="form-label font-weight-bold text-dark m-0">
                        <i class="fas fa-sitemap text-primary mr-1"></i> ចាត់ចែងទៅកាន់ភាគីពាក់ព័ន្ធ (អាចជ្រើសរើសបានច្រើន) <span class="text-danger">*</span>
                      </label>
                      <span class="badge badge-dark">
                        សរុប {{ dispatchForm.department_ids.length + dispatchForm.office_ids.length + dispatchForm.user_ids.length + (dispatchForm.working_group_ids ? dispatchForm.working_group_ids.length : 0) }} គោលដៅ
                      </span>
                    </div>

                    <!-- Section 1: Departments -->
                    <div class="mb-3 bg-white p-2 rounded border">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="font-13 text-primary"><i class="fas fa-building mr-1"></i> ១. នាយកដ្ឋានគោលដៅ៖</strong>
                        <span class="badge badge-primary">{{ dispatchForm.department_ids.length }} បានជ្រើស</span>
                      </div>
                      <div class="d-flex flex-wrap gap-2" style="max-height: 120px; overflow-y: auto;">
                        <label
                          v-for="d in recipientOptions.departments"
                          :key="d.id"
                          class="btn btn-xs mr-1 mb-1 font-khmer cursor-pointer border"
                          :class="dispatchForm.department_ids.includes(d.id) ? 'btn-primary text-white' : 'btn-light text-dark'"
                        >
                          <input
                            type="checkbox"
                            class="d-none"
                            :value="d.id"
                            v-model="dispatchForm.department_ids"
                          />
                          <i class="fas" :class="dispatchForm.department_ids.includes(d.id) ? 'fa-check-circle mr-1' : 'fa-plus-circle text-muted mr-1'"></i>
                          {{ d.name_kh }}
                        </label>
                      </div>
                    </div>

                    <!-- Section 2: Offices -->
                    <div class="mb-3 bg-white p-2 rounded border">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="font-13 text-info"><i class="fas fa-door-closed mr-1"></i> ២. ការិយាល័យគោលដៅ៖</strong>
                        <span class="badge badge-info">{{ dispatchForm.office_ids.length }} បានជ្រើស</span>
                      </div>
                      <div class="d-flex flex-wrap gap-2" style="max-height: 130px; overflow-y: auto;">
                        <label
                          v-for="o in recipientOptions.offices"
                          :key="o.id"
                          class="btn btn-xs mr-1 mb-1 font-khmer cursor-pointer border"
                          :class="dispatchForm.office_ids.includes(o.id) ? 'btn-info text-white' : 'btn-light text-dark'"
                        >
                          <input
                            type="checkbox"
                            class="d-none"
                            :value="o.id"
                            v-model="dispatchForm.office_ids"
                          />
                          <i class="fas" :class="dispatchForm.office_ids.includes(o.id) ? 'fa-check-circle mr-1' : 'fa-plus-circle text-muted mr-1'"></i>
                          {{ o.name_kh }}
                        </label>
                      </div>
                    </div>

                    <!-- Section 3: Specific Officers -->
                    <div class="bg-white p-2 rounded border">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="font-13 text-success"><i class="fas fa-user-check mr-1"></i> ៣. មន្ត្រីទទួលបន្ទុកជាក់លាក់៖</strong>
                        <span class="badge badge-success">{{ dispatchForm.user_ids.length }} បានជ្រើស</span>
                      </div>
                      <div class="input-group input-group-sm mb-2">
                        <input
                          type="text"
                          class="form-control"
                          placeholder="ស្វែងរកឈ្មោះមន្ត្រី..."
                          v-model="officerDispatchSearch"
                        />
                      </div>
                      <div class="d-flex flex-wrap gap-2" style="max-height: 140px; overflow-y: auto;">
                        <label
                          v-for="u in filteredOfficerOptions"
                          :key="u.id"
                          class="btn btn-xs mr-1 mb-1 font-khmer cursor-pointer border"
                          :class="dispatchForm.user_ids.includes(u.id) ? 'btn-success text-white' : 'btn-light text-dark'"
                        >
                          <input
                            type="checkbox"
                            class="d-none"
                            :value="u.id"
                            v-model="dispatchForm.user_ids"
                          />
                          <i class="fas" :class="dispatchForm.user_ids.includes(u.id) ? 'fa-check-circle mr-1' : 'fa-plus-circle text-muted mr-1'"></i>
                          {{ u.name_kh || u.name }} <small v-if="u.position?.title_kh">({{ u.position.title_kh }})</small>
                        </label>
                      </div>
                    </div>

                    <!-- Section 4: Working Groups -->
                    <div class="mt-3 bg-white p-2 rounded border">
                      <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong class="font-13 text-indigo"><i class="fas fa-users-cog mr-1"></i> ៤. ក្រុមការងារ (Working Groups)៖</strong>
                        <span class="badge badge-indigo text-white">{{ dispatchForm.working_group_ids ? dispatchForm.working_group_ids.length : 0 }} បានជ្រើស</span>
                      </div>
                      <div class="d-flex flex-wrap gap-2" style="max-height: 130px; overflow-y: auto;">
                        <label
                          v-for="wg in recipientOptions.working_groups"
                          :key="wg.id"
                          class="btn btn-xs mr-1 mb-1 font-khmer cursor-pointer border"
                          :class="dispatchForm.working_group_ids && dispatchForm.working_group_ids.includes(wg.id) ? 'btn-indigo text-white' : 'btn-light text-dark'"
                        >
                          <input
                            type="checkbox"
                            class="d-none"
                            :value="wg.id"
                            v-model="dispatchForm.working_group_ids"
                          />
                          <i class="fas" :class="dispatchForm.working_group_ids && dispatchForm.working_group_ids.includes(wg.id) ? 'fa-check-circle mr-1' : 'fa-plus-circle text-muted mr-1'"></i>
                          {{ wg.name_kh }}
                          <span class="badge badge-light border ml-1 text-xs" v-if="wg.leader_name_kh && wg.leader_name_kh !== 'មិនទាន់កំណត់'">
                            ប្រធាន៖ {{ wg.leader_name_kh }}
                          </span>
                        </label>
                        <div v-if="!recipientOptions.working_groups || recipientOptions.working_groups.length === 0" class="text-muted text-xs p-1">
                          មិនទាន់មានក្រុមការងារត្រូវបានកំណត់ឡើយ
                        </div>
                      </div>
                    </div>
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
    </Teleport>

    <!-- ==================== MODAL 5: ដាក់ស្នើ ឬរក្សាទុកព្រាងលិខិតឆ្លើយតប (SUBMIT / DRAFT RESPONSE) ==================== -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showSubmitResponseModal">
        <div class="modal-dialog modal-xl modal-dialog-centered my-auto" style="width: 100%; max-width: 1100px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden d-flex flex-column" style="max-height: 92vh;">
            <div class="modal-header bg-dark-custom text-white py-3 px-4 flex-shrink-0 align-items-center">
              <div class="d-flex align-items-center">
                <i class="fas fa-file-signature text-warning mr-2 fa-lg"></i>
                <div>
                  <h5 class="modal-title font-khmer font-weight-bold mb-0">
                    រៀបចំលិខិតឆ្លើយតប (របាយការណ៍ / កំណត់បង្ហាញ)
                  </h5>
                  <small class="text-white-50">សរសេរលើប្រព័ន្ធដូច File Word និងឆ្លងតាមឋានានុក្រមរហូតដល់ឯកឧត្តមអគ្គនាយក</small>
                </div>
              </div>
              <button type="button" class="close text-white" @click="showSubmitResponseModal = false">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            
            <div class="modal-body p-4 font-khmer flex-grow-1" style="overflow-y: auto; background-color: #f8f9fa;">
              <!-- Original Document Reference Banner -->
              <div class="alert alert-secondary py-2 px-3 font-13 mb-3 border">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                  <div>
                    ឆ្លើយតបលើឯកសារលេខ៖ <strong>{{ selectedDoc?.dg_inbound_number || selectedDoc?.general_inbound_number }}</strong>
                    <span class="mx-2">•</span>
                    កម្មវត្ថុដើម៖ <em>{{ selectedDoc?.title }}</em>
                  </div>
                  <div v-if="selectedDoc?.dg_annotation">
                    ចំណារអគ្គនាយក៖ <span class="text-danger font-weight-bold">{{ selectedDoc?.dg_annotation }}</span>
                  </div>
                </div>
              </div>

              <!-- Top Controls: Document Type & Subject -->
              <div class="card border rounded-lg p-3 bg-white mb-3">
                <div class="row">
                  <!-- Response Type Selector -->
                  <div class="col-md-4 mb-2">
                    <label class="form-label font-weight-bold">ទម្រង់លិខិតឆ្លើយតប <span class="text-danger">*</span></label>
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                      <label class="btn btn-outline-primary btn-sm font-khmer" :class="{ active: responseForm.response_type === 'REPORT' }">
                        <input type="radio" value="REPORT" v-model="responseForm.response_type" /> <i class="fas fa-file-alt mr-1"></i> របាយការណ៍
                      </label>
                      <label class="btn btn-outline-info btn-sm font-khmer" :class="{ active: responseForm.response_type === 'SUBMISSION_NOTE' }">
                        <input type="radio" value="SUBMISSION_NOTE" v-model="responseForm.response_type" /> <i class="fas fa-file-signature mr-1"></i> កំណត់បង្ហាញ
                      </label>
                      <label class="btn btn-outline-secondary btn-sm font-khmer" :class="{ active: responseForm.response_type === 'LETTER' }">
                        <input type="radio" value="LETTER" v-model="responseForm.response_type" /> លិខិតទូទៅ
                      </label>
                    </div>
                  </div>

                  <!-- Title / Subject -->
                  <div class="col-md-8 mb-2">
                    <label class="form-label font-weight-bold">កម្មវត្ថុលិខិតឆ្លើយតប <span class="text-danger">*</span></label>
                    <input
                      type="text"
                      class="form-control"
                      placeholder="ឧ. របាយការណ៍ស្តីពី..."
                      v-model="responseForm.title"
                      required
                    />
                  </div>
                </div>
              </div>

              <!-- Word-like Editor Component -->
              <div class="mb-3">
                <WordEditor
                  v-model="responseForm.content"
                  :doc-type="responseForm.response_type"
                  :organization-name="userStore.user?.department?.name_kh || 'អគ្គនាយកដ្ឋាន...'"
                  :department-name="userStore.user?.office?.name_kh || 'នាយកដ្ឋាន...'"
                  :officer-name="userStore.user?.name_kh || userStore.user?.name"
                  :officer-position="userStore.user?.position?.title_kh || 'មន្ត្រី'"
                />
              </div>

              <!-- Hierarchy Approver Selection & Attachment -->
              <div class="card border rounded-lg p-3 bg-white mb-2">
                <div class="row">
                  <!-- Next Approver -->
                  <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label font-weight-bold">
                      <i class="fas fa-user-check text-success mr-1"></i>ជ្រើសរើសថ្នាក់ដឹកនាំដើម្បីឆ្លងបន្ត (Hierarchy Approver)
                    </label>
                    <select class="form-control font-weight-bold text-dark" v-model="responseForm.forwarded_to_id">
                      <option value="">-- ជ្រើសរើសថ្នាក់ដឹកនាំបន្ទាប់ (ឬទុកនៅទទេបើគ្រាន់តែរក្សាទុកព្រាង) --</option>
                      <option v-for="c in responseCandidates" :key="c.id" :value="c.id">
                        {{ c.name_kh || c.name }} - {{ c.position?.title_kh || '' }} ({{ c.office?.name_kh || c.department?.name_kh || '' }})
                      </option>
                    </select>
                    <small class="text-muted">ជ្រើសរើសដើម្បីដាក់ស្នើឆ្លងឡើងលើ ឬទុកទទេដើម្បីគ្រាន់តែ «រក្សាទុកព្រាង»</small>
                  </div>

                  <!-- Optional Scanned Attachment -->
                  <div class="col-md-6">
                    <label class="form-label font-weight-bold">
                      <i class="fas fa-paperclip text-primary mr-1"></i>ភ្ជាប់ឯកសារ Scan បន្ថែម (បើមាន)
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
                        {{ selectedResponseFileName || 'ជ្រើសរើសឯកសារភ្ជាប់បន្ថែម...' }}
                      </label>
                    </div>
                  </div>
                </div>

                <div class="mt-2" v-if="responseForm.forwarded_to_id">
                  <label class="form-label font-weight-bold small text-muted">កំណត់សម្គាល់ឆ្លង / មតិគោរពជូន</label>
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    placeholder="សូមគោរពជូនឯកឧត្តម/លោក/លោកស្រី..."
                    v-model="responseForm.comment"
                  />
                </div>
              </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light py-2 px-4 justify-content-between">
              <button type="button" class="btn btn-secondary px-3" @click="showSubmitResponseModal = false">
                បិទផ្ទាំង
              </button>
              <div>
                <button
                  type="button"
                  class="btn btn-outline-primary px-3 mr-2"
                  @click="saveResponseDraftOnly"
                  :disabled="submitting"
                >
                  <i class="fas fa-save mr-1"></i> រក្សាទុកព្រាង (Save Draft)
                </button>
                <button
                  type="button"
                  class="btn btn-warning px-4 font-weight-bold text-dark"
                  @click="submitResponseDraftAction"
                  :disabled="submitting"
                >
                  <span v-if="submitting" class="spinner-border spinner-border-sm mr-1"></span>
                  <i class="fas fa-paper-plane mr-1"></i> ដាក់ស្នើឆ្លងតាមឋានានុក្រម
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ==================== MODAL 6: ថ្នាក់ដឹកនាំពិនិត្យ & ចារឆ្លងលិខិតឆ្លើយតប (REVIEW ACTION) ==================== -->
    <Teleport to="body">
      <div class="custom-modal-backdrop" v-if="showReviewResponseModal">
        <div class="modal-dialog modal-lg modal-dialog-centered my-auto" style="width: 100%; max-width: 800px;">
          <div class="modal-content shadow-2xl border-0 rounded-lg overflow-hidden">
            <div class="modal-header bg-dark-custom text-white py-3 px-4 flex-shrink-0 align-items-center">
              <h5 class="modal-title font-khmer font-weight-bold mb-0">
                <i class="fas fa-clipboard-check text-warning mr-2"></i>ពិនិត្យ និងចារឆ្លងលិខិតឆ្លើយតប
              </h5>
              <button type="button" class="close text-white" @click="showReviewResponseModal = false">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body p-4 font-khmer" style="max-height: calc(85vh - 120px); overflow-y: auto;">
              <div class="alert alert-secondary py-2 font-13 mb-3">
                ឯកសារដើមលេខ៖ <strong>{{ selectedDoc?.dg_inbound_number }}</strong><br />
                លិខិតឆ្លើយតប៖ <strong>{{ selectedResponse?.title }}</strong><br />
                អ្នករៀបចំ៖ <strong>{{ selectedResponse?.drafted_by_user?.name_kh }}</strong>
                <div class="mt-2 d-flex flex-wrap" v-if="selectedResponse?.file_path">
                  <button
                    type="button"
                    class="btn btn-xs btn-outline-info mr-2"
                    @click="previewResponseDoc(selectedResponse)"
                  >
                    <i class="fas fa-eye mr-1"></i> មើលឯកសារព្រាង (PDF)
                  </button>
                  <a :href="getResponseFileUrl(selectedResponse.id)" download class="btn btn-xs btn-outline-secondary">
                    <i class="fas fa-download mr-1"></i> ទាញយក
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
    </Teleport>

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
                        <div class="mt-2 d-flex flex-wrap" v-if="resp.file_path">
                          <button
                            type="button"
                            class="btn btn-xs btn-outline-info mr-2 font-khmer"
                            @click="previewResponseDoc(resp)"
                          >
                            <i class="fas fa-eye mr-1"></i> មើលព្រាង (PDF)
                          </button>
                          <a :href="getResponseFileUrl(resp.id)" download class="btn btn-xs btn-outline-secondary">
                            <i class="fas fa-download mr-1"></i> ទាញយក
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
            <div class="modal-footer font-khmer justify-content-between bg-light py-2 px-4 border-top flex-shrink-0">
              <div>
                <!-- Action: Cascading Forward / Re-assign Downward -->
                <button
                  v-if="selectedDoc && canForward(selectedDoc)"
                  class="btn btn-info font-weight-bold text-white shadow-sm"
                  title="ចាត់ចែង និងបញ្ជូនបន្តការងារទៅកាន់មន្ត្រីក្រោមឱវាទ"
                  @click="showDetailModal = false; openForwardModal(selectedDoc)"
                >
                  <i class="fas fa-directions mr-1"></i> ចាត់ចែង និងបញ្ជូនបន្ត
                </button>
              </div>
              <div class="d-flex align-items-center">
                <button
                  v-if="selectedDoc && canEditDocument(selectedDoc)"
                  class="btn btn-outline-warning text-dark font-weight-bold mr-2"
                  title="កែប្រែព័ត៌មានឯកសារនេះ (ករណីបញ្ចូលខុស)"
                  @click="openEditModal(selectedDoc)"
                >
                  <i class="fas fa-edit mr-1"></i> កែប្រែព័ត៌មាន
                </button>
                <button
                  v-if="selectedDoc && canDeleteDocument(selectedDoc)"
                  class="btn btn-outline-danger font-weight-bold mr-2"
                  title="លុបឯកសារចូលនេះចេញ (ករណីបញ្ចូលខុស)"
                  @click="deleteDocumentAction(selectedDoc)"
                >
                  <i class="fas fa-trash-alt mr-1"></i> លុបឯកសារ
                </button>
                <button
                  v-if="selectedDoc && canDraftResponse(selectedDoc)"
                  class="btn btn-warning text-dark font-weight-bold mr-2"
                  @click="showDetailModal = false; openSubmitResponseModal(selectedDoc)"
                >
                  <i class="fas fa-reply mr-1"></i> បង្កើតរបាយការណ៍ / លិខិតឆ្លើយតប
                </button>
                <button type="button" class="btn btn-secondary" @click="showDetailModal = false">បិទ</button>
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
                  :href="pdfModalUrl"
                  target="_blank"
                  class="btn btn-xs btn-outline-light mr-2 font-khmer"
                  title="បើកក្នុងផ្ទាំងថ្មី (New Tab)"
                >
                  <i class="fas fa-external-link-alt mr-1"></i> បើកផ្ទាំងថ្មី
                </a>
                <a
                  :href="pdfModalDownloadUrl"
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
              <iframe
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
import { useUserStore } from '@/stores/user';
import WordEditor from '@/components/common/WordEditor.vue';
import DocumentTrackingFlowModal from '@/components/common/DocumentTrackingFlowModal.vue';
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

// --- PDF Viewer Modal State ---
const showPdfModal = ref(false);
const pdfModalTitle = ref('');
const pdfModalUrl = ref('');
const pdfModalDownloadUrl = ref('');
const isPdfLoading = ref(true);

const openPdfPreview = (title, viewUrl, downloadUrl) => {
  pdfModalTitle.value = title || 'មើលឯកសារ PDF';
  pdfModalUrl.value = viewUrl;
  pdfModalDownloadUrl.value = downloadUrl || viewUrl;
  isPdfLoading.value = true;
  showPdfModal.value = true;
};

const closePdfModal = () => {
  showPdfModal.value = false;
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

const reviewActionForm = reactive({
  action: 'FORWARD',
  forwarded_to_id: '',
  response_number: '',
  comment: '',
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
  if (isDeptDirector) {
    opts.push({ value: 'OFFICE', label: 'ការិយាល័យក្រោមឱវាទ', icon: 'fas fa-door-closed text-info' });
    opts.push({ value: 'OFFICER', label: 'មន្ត្រីក្រោមឱវាទក្នុងនាយកដ្ឋាន', icon: 'fas fa-user-circle text-primary' });
  } else if (isDeptLeader) {
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

  if (myWfConfig.value) {
    if (myWfConfig.value.send_type === 'ALL') return offices;
    if (myWfConfig.value.send_type === 'CUSTOM') {
      const allowed = (myWfConfig.value.send_office_ids || []).map(Number);
      return offices.filter(o => allowed.includes(Number(o.id)));
    }
    if (myWfConfig.value.send_type === 'DEPARTMENT' && userStore.department_id) {
      return offices.filter(o => Number(o.department_id) === Number(userStore.department_id));
    }
    return [];
  }

  if (userStore.department_id) {
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
const canAcknowledge = (doc) => {
  if (doc.status !== 'DISPATCHED' || doc.is_response_required) return false;
  if (isAdmin.value) return true;
  // If targeted specifically to this user
  if (doc.target_user_id) {
    return doc.target_user_id === userStore.id;
  }
  // If targeted to office without specific user -> only Office Chief / Deputy Chief (level <= 6)
  if (doc.target_office_id && doc.target_office_id === userStore.office_id) {
    return userStore.position && Number(userStore.position.level) <= 6;
  }
  // If targeted to department without office/user -> only Department Director / Deputy Director (level <= 4)
  if (doc.target_department_id && doc.target_department_id === userStore.department_id) {
    return userStore.position && Number(userStore.position.level) <= 4;
  }
  return false;
};

const canDraftResponse = (doc) => {
  if (!doc) return false;
  if (!['DISPATCHED', 'IN_RESPONSE_PROGRESS'].includes(doc.status)) return false;

  const latest = doc.latest_response;
  if (latest) {
    if (['DRAFT', 'RETURNED'].includes(latest.status) && (latest.drafted_by === userStore.id || isAdmin.value)) {
      return true;
    }
    return false;
  }

  // គ្រប់គ្នាដែលស្ថិតនៅក្រោមអគ្គនាយក សុទ្ធតែមានសិទ្ធិបង្កើតរបាយការណ៍ (Everyone below DG has the right to create report)
  if (isAdmin.value) return true;
  if (isDg.value) return false; // DG is the final approver, not the drafter
  return true;
};

const canReviewResponse = (doc) => {
  const latest = doc.latest_response;
  if (!latest || latest.status !== 'UNDER_REVIEW') return false;
  return isAdmin.value || latest.current_approver_id === userStore.id;
};

const canForward = (doc) => {
  if (!doc) return false;
  if (['COMPLETED', 'CANCELLED', 'RECEPTION_DRAFT', 'SUBMITTED_TO_ASSISTANT', 'SUBMITTED_TO_DG'].includes(doc.status)) return false;
  if (isAdmin.value) return true;
  // DG, Deputy DG, Assistant
  if (userStore.position && Number(userStore.position.level) <= 2) return true;
  if (canAssist.value) return true;

  const userLevel = userStore.position ? Number(userStore.position.level) : 999;
  const userDeptId = userStore.department_id ? Number(userStore.department_id) : null;
  const userOfficeId = userStore.office_id ? Number(userStore.office_id) : null;
  const userId = Number(userStore.id);

  const docDeptId = Number(doc.target_department_id || doc.target_department?.id);
  const docOfficeId = Number(doc.target_office_id || doc.target_office?.id);
  const docUserId = Number(doc.target_user_id || doc.target_user?.id);

  // 1. Department Director / Acting (Level <= 4): can forward if doc is in their department
  if (userLevel <= 4 && userDeptId) {
    if (docDeptId === userDeptId) return true;
    if (doc.assignments?.some(a => Number(a.department_id || a.department?.id) === userDeptId)) return true;
  }

  // 2. Office leadership (Level <= 8): can forward if doc is in their office
  if (userLevel <= 8 && userOfficeId) {
    if (docOfficeId === userOfficeId) return true;
    if (doc.assignments?.some(a => Number(a.office_id || a.office?.id) === userOfficeId)) return true;
  }

  // 3. User specifically assigned / forwarded this document (Deputy Director Level 5, or assigned officer with subordinates)
  if (docUserId === userId) return true;
  if (doc.assignments?.some(a => Number(a.user_id || a.user?.id) === userId)) return true;

  // 4. Working Group leader / deputy leader
  if (isUserLeaderOfAssignedWorkingGroup(doc)) {
    return true;
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
        subText: 'រង់ចាំចុះលេខ អ.ន. & ដាក់ជូន ឯ.ឧ.'
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
        const primary = doc.assignments[0];
        if (primary.target_type === 'USER' && primary.user) {
          return {
            label: primary.user.name_kh || primary.user.name,
            badgeClass: 'badge-primary',
            icon: 'fas fa-user',
            subText: primary.office?.name_kh || primary.department?.name_kh || 'មន្ត្រីទទួលបន្ទុក'
          };
        } else if (primary.target_type === 'WORKING_GROUP' && primary.working_group) {
          return {
            label: primary.working_group.name_kh,
            badgeClass: 'badge-teal text-white',
            icon: 'fas fa-users-cog',
            subText: 'ក្រុមការងារចាត់តាំង'
          };
        } else if (primary.target_type === 'OFFICE' && primary.office) {
          return {
            label: primary.office.name_kh,
            badgeClass: 'badge-primary',
            icon: 'fas fa-door-closed',
            subText: primary.department?.name_kh || 'ការិយាល័យទទួលបន្ទុក'
          };
        } else if (primary.target_type === 'DEPARTMENT' && primary.department) {
          return {
            label: primary.department.name_kh,
            badgeClass: 'badge-primary',
            icon: 'fas fa-building',
            subText: 'នាយកដ្ឋានទទួលបន្ទុក'
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

// កំណត់ស្ថានភាពសាមញ្ញ ៣ ដំណាក់កាល (Pending, Doing, Complete)
const getSimplifiedStatus = (doc) => {
  if (!doc) return { label: 'Pending', khLabel: 'ចូលដំបូង / រង់ចាំ', key: 'PENDING', badgeClass: 'badge-warning text-dark font-weight-bold', icon: 'fas fa-clock', subText: '' };

  const status = doc.status;
  if (['RECEPTION_DRAFT', 'SUBMITTED_TO_ASSISTANT', 'SUBMITTED_TO_DG'].includes(status)) {
    let sub = 'ចូលមកដំបូង';
    if (status === 'RECEPTION_DRAFT') sub = 'នៅតុចុះបញ្ជីទទួល';
    else if (status === 'SUBMITTED_TO_ASSISTANT') sub = 'នៅតុជំនួយការ';
    else if (status === 'SUBMITTED_TO_DG') sub = 'នៅតុអគ្គនាយក';

    return {
      label: 'Pending',
      khLabel: 'ករណីចូលមកដំបូង',
      key: 'PENDING',
      badgeClass: 'badge-warning text-dark font-weight-bold',
      icon: 'fas fa-clock',
      subText: sub
    };
  }

  if (['DG_ANNOTATED', 'DISPATCHED', 'IN_RESPONSE_PROGRESS'].includes(status)) {
    let sub = 'កំពុងដំណើរការ';
    if (status === 'DG_ANNOTATED') sub = 'អគ្គនាយកចាររួច (រង់ចាំចែកចាយ)';
    else if (status === 'DISPATCHED') sub = 'បានបញ្ជូនចេញ / ចាត់ចែង';
    else if (status === 'IN_RESPONSE_PROGRESS') sub = 'កំពុងរៀបចំឆ្លើយតប/ឆ្លង';

    return {
      label: 'Doing',
      khLabel: 'បានបញ្ជូនចេញ ឬកំពុងធ្វើ',
      key: 'DOING',
      badgeClass: 'badge-primary font-weight-bold',
      icon: 'fas fa-tasks',
      subText: sub
    };
  }

  if (status === 'COMPLETED') {
    return {
      label: 'Complete',
      khLabel: 'ករណីអគ្គនាយកឯកភាព',
      key: 'COMPLETE',
      badgeClass: 'badge-success font-weight-bold',
      icon: 'fas fa-check-circle',
      subText: 'អគ្គនាយកបានឯកភាព'
    };
  }

  return {
    label: doc.status || 'Pending',
    khLabel: doc.status_kh || 'ចូលដំបូង',
    key: 'PENDING',
    badgeClass: 'badge-secondary',
    icon: 'fas fa-info-circle',
    subText: ''
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
  if (t && ['all', 'pending', 'doing', 'completed', 'my_unit', 'reception', 'assistant_inbox', 'dg_inbox', 'my_todo', 'my_done', 'assigned_to_me', 'response_workflow'].includes(t)) {
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

const openDgAnnotateModal = (doc) => {
  selectedDoc.value = doc;
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
  submitting.value = true;
  try {
    const res = await apiDgAnnotate(selectedDoc.value.id, dgAnnotateForm);
    Swal.fire({
      icon: 'success',
      title: 'ជោគជ័យ!',
      text: res.data?.message || 'បានកត់ត្រាចំណារឯកឧត្តមអគ្គនាយករួចរាល់!',
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
  dispatchForm.target_type = 'MULTIPLE';
  dispatchForm.target_department_id = '';
  dispatchForm.target_office_id = '';
  dispatchForm.target_user_id = '';
  dispatchForm.department_ids = doc.target_department_id ? [doc.target_department_id] : [];
  dispatchForm.office_ids = doc.target_office_id ? [doc.target_office_id] : [];
  dispatchForm.user_ids = doc.target_user_id ? [doc.target_user_id] : [];
  dispatchForm.working_group_ids = (doc.assignments || [])
    .filter(a => a.target_type === 'WORKING_GROUP' && a.working_group_id)
    .map(a => a.working_group_id);
  dispatchForm.dispatch_notes = doc.dispatch_notes || '';
  showAssistantDispatchModal.value = true;
};

const submitAssistantDispatch = async () => {
  if (dispatchForm.department_ids.length === 0 && dispatchForm.office_ids.length === 0 && dispatchForm.user_ids.length === 0 && dispatchForm.working_group_ids.length === 0 && !dispatchForm.target_department_id && !dispatchForm.target_office_id && !dispatchForm.target_user_id) {
    Swal.fire({ icon: 'warning', title: 'សូមជ្រើសរើស', text: 'សូមជ្រើសរើសយ៉ាងហោចណាស់មួយនាយកដ្ឋាន ការិយាល័យ មន្ត្រី ឬក្រុមការងារគោលដៅ!' });
    return;
  }
  submitting.value = true;
  try {
    const formData = new FormData();
    formData.append('target_type', dispatchForm.target_type);
    dispatchForm.department_ids.forEach(id => formData.append('department_ids[]', id));
    dispatchForm.office_ids.forEach(id => formData.append('office_ids[]', id));
    dispatchForm.user_ids.forEach(id => formData.append('user_ids[]', id));
    dispatchForm.working_group_ids.forEach(id => formData.append('working_group_ids[]', id));
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
  const isSuper = isAdmin.value || (userStore.position && Number(userStore.position.level) <= 2) || canAssist.value;
  const isDeptDirector = userStore.position && Number(userStore.position.level) <= 4;
  const isDeptLeader = userStore.position && Number(userStore.position.level) <= 5;
  const isOfficeLeader = userStore.position && Number(userStore.position.level) <= 8;
  const isWgLeader = isUserLeaderOfAssignedWorkingGroup(doc);

  if (isDeptDirector) {
    forwardForm.target_type = 'OFFICE';
  } else if (isOfficeLeader || isDeptLeader) {
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
    responseForm.reference_note = `យោង៖ ចំណារលើឯកសារលេខ ${doc.dg_inbound_number || doc.general_inbound_number} ចុះថ្ងៃទី ${doc.received_date}`;
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
    const errorMsg = err.response?.data?.message || Object.values(err.response?.data?.errors || {})[0]?.[0] || 'មិនអាចដំណើរការសកម្មភាពបានឡើយ!';
    Swal.fire({ icon: 'error', title: 'បរាជ័យ', text: errorMsg });
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
  const title = `ឯកសារដើម៖ ${doc.dg_inbound_number || doc.general_inbound_number || ''} - ${doc.title || ''}`;
  openPdfPreview(title, getViewOriginalUrl(doc.id), getOriginalDownloadUrl(doc.id));
};

const previewAnnotatedDoc = (doc) => {
  const title = `ឯកសារមានចំណារ៖ ${doc.dg_inbound_number || doc.general_inbound_number || ''} - ${doc.title || ''}`;
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
</style>

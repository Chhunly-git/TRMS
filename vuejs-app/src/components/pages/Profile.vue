<template>
  <div class="content-wrapper" style="min-height: 1000px; background-color: #f4f6f9; padding-bottom: 40px;">
    
    <!-- Cover Banner Header -->
    <div class="profile-cover-banner position-relative">
      <div class="container-fluid d-flex justify-content-between align-items-center py-4 px-4">
        <h1 class="text-white font-weight-bold m-0" style="font-size: 20px;">
          <i class="fas fa-id-badge mr-2"></i>ព័ត៌មានប្រវត្តិរូបផ្ទាល់ខ្លួន
        </h1>
        <router-link :to="{ name: 'my-profile' }" class="btn btn-light btn-sm px-3 shadow-sm font-weight-bold">
          <i class="fas fa-print text-success mr-1"></i> បោះពុម្ពប្រវត្តិរូប (Print)
        </router-link>
      </div>
    </div>

    <!-- Main Content -->
    <section class="content px-3" style="margin-top: -30px;">
      <div class="container-fluid">
        <!-- Row 1: Left Profile Card & Right Personal Info Card (Equal Height) -->
        <div class="row align-items-stretch mb-4">
          
          <!-- ផ្នែកខាងឆ្វេង៖ Profile Card ទំនើប -->
          <div class="col-lg-4 d-flex mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm rounded-lg text-center p-4 bg-white w-100 d-flex flex-column justify-content-between profile-card-left mb-0">
              <div>
                <div class="position-relative d-inline-block mx-auto mb-3 mt-2">
                  <img 
                    class="rounded-circle shadow border border-white" 
                    :src="userStore.profile_image || emptyImage" 
                    alt="Profile"
                    style="width: 130px; height: 130px; object-fit: cover; border-width: 4px !important;"
                  >
                  <span class="position-absolute bottom-0 right-0 p-2 bg-success border border-white rounded-circle"></span>
                </div>
                
                <h4 class="font-weight-bold text-dark mb-1">{{ userStore.name_kh || userStore.name || '---' }}</h4>
                <p class="text-muted small mb-2">{{ userStore.name_en || '---' }}</p>
                
                <div class="mb-3">
                  <span class="badge badge-success px-3 py-2 rounded-pill font-weight-normal" style="font-size: 12px; background-color: rgba(40, 167, 69, 0.1); color: #28a745; border: 1px solid #28a745;">
                    {{ userStore.position?.title_kh || userStore.position?.name || 'មន្ត្រីរាជការ' }}
                  </span>
                </div>
              </div>

              <!-- ផ្នែកខាងក្រោមនៃកាតខាងឆ្វេង -->
              <div class="border-top pt-3 text-left mt-auto">
                <div class="d-flex justify-content-between mb-2 small" v-if="userStore.employee_type === 'CIVIL_SERVICE'">
                  <span class="text-muted text-nowrap mr-2"><i class="fas fa-id-card mr-1 text-success"></i> អត្តលេខ:</span>
                  <strong class="text-dark text-right">{{ userStore.employee_code || '---' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                  <span class="text-muted text-nowrap mr-2"><i class="fas fa-building mr-1 text-success"></i> នាយកដ្ឋាន:</span>
                  <strong class="text-dark text-right text-truncate-custom">{{ userStore.department?.name_kh || '---' }}</strong>
                </div>
                <div class="d-flex justify-content-between small">
                  <span class="text-muted text-nowrap mr-2"><i class="fas fa-envelope mr-1 text-success"></i> អ៊ីមែល:</span>
                  <strong class="text-dark text-right text-truncate-custom">{{ userStore.email || '---' }}</strong>
                </div>
              </div>
            </div>
          </div>

          <!-- ផ្នែកខាងស្តាំ៖ ព័ត៌មានផ្ទាល់ខ្លួន -->
          <div class="col-lg-8 d-flex">
            <div class="card border-0 shadow-sm rounded-lg bg-white w-100 mb-0">
              <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="font-weight-bold text-success m-0 d-flex align-items-center">
                  <div class="icon-circle bg-light-success text-success mr-2">
                    <i class="fas fa-user-circle"></i>
                  </div>
                  ១. ព័ត៌មានផ្ទាល់ខ្លួន
                </h5>
              </div>
              <div class="card-body px-4 py-3">
                <!-- ឈ្មោះមន្ត្រី & អត្តលេខ -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-signature text-success mr-1"></i> គោត្តនាម និងនាម (ខ្មែរ)</span>
                    <strong class="text-dark font-lg">{{ userStore.name_kh || userStore.name || '---' }}</strong>
                  </div>
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-font text-success mr-1"></i> ជាអក្សរឡាតាំង (English)</span>
                    <strong class="text-dark text-uppercase font-lg">{{ userStore.name_en || '---' }}</strong>
                  </div>
                </div>

                <div class="row" v-if="userStore.employee_type === 'CIVIL_SERVICE'">
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-id-card-alt text-success mr-1"></i> អត្តលេខមន្ត្រីរាជការ</span>
                    <strong class="text-dark">{{ userStore.employee_code || '---' }}</strong>
                  </div>
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-credit-card text-success mr-1"></i> លេខប័ណ្ណសម្គាល់មន្ត្រីកសហវ</span>
                    <strong class="text-dark">{{ userStore.mef_card_number || '---' }}</strong>
                  </div>
                </div>

                <hr class="my-2 border-light">

                <!-- ភេទ, ថ្ងៃខែឆ្នាំកំណើត, ស្ថានភាពគ្រួសារ -->
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-venus-mars text-success mr-1"></i> ភេទ</span>
                    <strong class="text-dark">{{ formatGender(userStore.gender) }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-calendar-alt text-success mr-1"></i> ថ្ងៃខែឆ្នាំកំណើត</span>
                    <strong class="text-dark">{{ formatDate(userStore.dob) }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-heart text-success mr-1"></i> ស្ថានភាពគ្រួសារ</span>
                    <strong class="text-dark">{{ formatMaritalStatus(userStore.marital_status) }}</strong>
                  </div>
                </div>

                <!-- ទីកន្លែងកំណើត, អាសយដ្ឋានបច្ចុប្បន្ន -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-map-marker-alt text-success mr-1"></i> ទីកន្លែងកំណើត</span>
                    <strong class="text-dark">{{ userStore.birth_place || '---' }}</strong>
                  </div>
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-home text-success mr-1"></i> អាសយដ្ឋានបច្ចុប្បន្ន</span>
                    <strong class="text-dark">{{ userStore.current_address || '---' }}</strong>
                  </div>
                </div>

                <!-- លេខទូរសព្ទ, អ៊ីមែល -->
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-phone text-success mr-1"></i> លេខទូរសព្ទ</span>
                    <strong class="text-dark">{{ userStore.phone || '---' }}</strong>
                  </div>
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-envelope text-success mr-1"></i> អ៊ីមែល</span>
                    <strong class="text-dark">{{ userStore.email || '---' }}</strong>
                  </div>
                </div>

                <hr class="my-2 border-light">

                <!-- អត្តសញ្ញាណប័ណ្ណ & លិខិតឆ្លងដែន -->
                <div class="row">
                  <div class="col-md-6 mb-2">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-address-card text-success mr-1"></i> អត្តសញ្ញាណប័ណ្ណសញ្ជាតិខ្មែរ</span>
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <strong class="text-dark">{{ userStore.national_id_number || '---' }}</strong>
                        <a v-if="userStore.national_id_file" :href="getFullImageUrl(userStore.national_id_file)" target="_blank" class="btn btn-xs btn-outline-success ml-2 py-0">
                          <i class="fas fa-eye"></i> មើលឯកសារ
                        </a>
                      </div>
                      <span v-if="userStore.national_id_expired_date" class="text-muted small">ផុតកំណត់៖ {{ formatDate(userStore.national_id_expired_date) }}</span>
                    </div>
                  </div>
                  <div class="col-md-6 mb-2">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-passport text-success mr-1"></i> លិខិតឆ្លងដែន (Passport)</span>
                    <div class="d-flex justify-content-between align-items-center">
                      <div>
                        <strong class="text-dark">{{ userStore.passport_number || '---' }}</strong>
                        <a v-if="userStore.passport_file" :href="getFullImageUrl(userStore.passport_file)" target="_blank" class="btn btn-xs btn-outline-success ml-2 py-0">
                          <i class="fas fa-eye"></i> មើលឯកសារ
                        </a>
                      </div>
                      <span v-if="userStore.passport_expired_date" class="text-muted small">ផុតកំណត់៖ {{ formatDate(userStore.passport_expired_date) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
        </div>

        <!-- Row 2: ព័ត៌មានអំពីស្ថានភាពមុខងារ (Full Width Below) -->
        <div class="row">
          <div class="col-12">
            <!-- Card 2: ព័ត៌មានស្ថានភាពមុខងារ -->
            <div class="card border-0 shadow-sm rounded-lg bg-white mb-4">
              <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="font-weight-bold text-success m-0 d-flex align-items-center">
                  <div class="icon-circle bg-light-success text-success mr-2">
                    <i class="fas fa-briefcase"></i>
                  </div>
                  ២. ព័ត៌មានអំពីស្ថានភាពមុខងារ
                </h5>
              </div>
              <div class="card-body px-4 py-3">
                <h6 class="font-weight-bold text-success mb-3 border-left-custom pl-2" style="font-size: 14px;">ក. ចូលបម្រើការងារដំបូង</h6>
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-calendar-check text-success mr-1"></i> កាលបរិច្ឆេទចូលបម្រើការងារ</span>
                    <strong class="text-dark">{{ formatDate(userStore.first_service_date) }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-calendar-day text-success mr-1"></i> កាលបរិច្ឆេទតាំងស៊ុប់</span>
                    <strong class="text-dark">{{ formatDate(userStore.first_appointment_date) }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-tags text-success mr-1"></i> ក្របខណ្ឌ ឋានន្តរស័ក្តិ និងថ្នាក់</span>
                    <strong class="text-dark text-uppercase">{{ userStore.initial_framework || '---' }}</strong>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-user-tag text-success mr-1"></i> មុខតំណែងដំបូង</span>
                    <strong class="text-dark">{{ userStore.initial_position || '---' }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-university text-success mr-1"></i> ក្រសួង/ស្ថាប័ន</span>
                    <strong class="text-dark">{{ userStore.initial_ministry || '---' }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-network-wired text-success mr-1"></i> អង្គភាព</span>
                    <strong class="text-dark">{{ userStore.initial_unit || '---' }}</strong>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-building text-success mr-1"></i> នាយកដ្ឋានដំបូង</span>
                    <strong class="text-dark">{{ userStore.initial_department || '---' }}</strong>
                  </div>
                  <div class="col-md-6 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-door-open text-success mr-1"></i> ការិយាល័យដំបូង</span>
                    <strong class="text-dark">{{ userStore.initial_office || '---' }}</strong>
                  </div>
                </div>

                <hr class="my-3 border-light">

                <h6 class="font-weight-bold text-success mb-3 mt-2 border-left-custom pl-2" style="font-size: 14px;">ខ. ស្ថានភាពមុខងារបច្ចុប្បន្ន</h6>
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-building text-success mr-1"></i> នាយកដ្ឋាន</span>
                    <strong class="text-dark text-uppercase">{{ userStore.department?.name_kh || '---' }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-door-open text-success mr-1"></i> ការិយាល័យ</span>
                    <strong class="text-dark text-uppercase">{{ userStore.office?.name_kh || '---' }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-user-tie text-success mr-1"></i> មុខតំណែង</span>
                    <strong class="text-dark text-uppercase">{{ userStore.position?.title_kh || userStore.position?.name || '---' }}</strong>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-shield-alt text-success mr-1"></i> ក្របខណ្ឌបច្ចុប្បន្ន</span>
                    <strong class="text-dark">{{ userStore.current_framework || '---' }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-calendar-check text-success mr-1"></i> កាលបរិច្ឆេទប្តូរក្របខណ្ឌចុងក្រោយ</span>
                    <strong class="text-dark">{{ formatDate(userStore.current_appointment_date) }}</strong>
                  </div>
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-calendar-day text-success mr-1"></i> កាលបរិច្ឆេទទទួលតំណែងចុងក្រោយ</span>
                    <strong class="text-dark">{{ formatDate(userStore.current_position_date) }}</strong>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-4 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-user-tag text-success mr-1"></i> ស្ថានភាពមន្ត្រី</span>
                    <span :class="getOfficerStatusBadgeClass(userStore.officer_status)">
                      {{ formatOfficerStatus(userStore.officer_status) }}
                    </span>
                    <small v-if="userStore.officer_status_date && userStore.officer_status !== 'ACTIVE'" class="text-muted d-block mt-1">
                      កាលបរិច្ឆេទ៖ {{ formatDate(userStore.officer_status_date) }}
                    </small>
                    <small v-if="userStore.officer_status_reason && userStore.officer_status !== 'ACTIVE'" class="text-secondary d-block">
                      មូលហេតុ៖ {{ userStore.officer_status_reason }}
                    </small>
                  </div>
                  <div class="col-md-8 mb-3">
                    <span class="text-muted small d-block mb-1"><i class="fas fa-business-time text-success mr-1"></i> រយៈពេលបម្រើការងារ (អតីតភាពការងារ)</span>
                    <strong class="text-primary font-weight-bold" style="font-size: 15px;">
                      {{ userStore.service_duration_formatted || calculateServiceDuration(userStore.first_service_date || userStore.first_appointment_date, userStore.officer_status !== 'ACTIVE' ? userStore.officer_status_date : null) || '---' }}
                    </strong>
                  </div>
                </div>

                <!-- គ. តួនាទីបន្ថែមលើមុខងារបច្ចុប្បន្ន -->
                <div v-if="userStore.employee_type === 'CIVIL_SERVICE' && userStore.additional_positions?.length > 0" class="mt-4">
                  <hr class="my-3 border-light">
                  <h6 class="font-weight-bold text-success mb-3 border-left-custom pl-2" style="font-size: 14px;">គ. តួនាទីបន្ថែមលើមុខងារបច្ចុប្បន្ន</h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0">
                      <thead class="bg-light">
                        <tr class="small text-muted font-weight-bold">
                          <th style="width: 50px" class="text-center">ល.រ</th>
                          <th style="width: 130px">កាលបរិច្ឆេទ</th>
                          <th>ឯកសារយោង</th>
                          <th>មុខតំណែង</th>
                          <th>ឋានៈស្មើ</th>
                          <th>អង្គភាព</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(row, idx) in userStore.additional_positions" :key="row.id" class="small">
                          <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                          <td>{{ formatDate(row.date) }}</td>
                          <td>{{ row.document || '---' }}</td>
                          <td>{{ row.position || '---' }}</td>
                          <td>{{ row.equivalent_status || '---' }}</td>
                          <td>{{ row.unit || '---' }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- ឃ. ស្ថានភាពស្ថិតនៅក្រៅក្របខណ្ឌដើម -->
                <div v-if="userStore.employee_type === 'CIVIL_SERVICE' && userStore.out_of_framework_statuses?.length > 0" class="mt-4">
                  <hr class="my-3 border-light">
                  <h6 class="font-weight-bold text-success mb-3 border-left-custom pl-2" style="font-size: 14px;">ឃ. ស្ថានភាពស្ថិតនៅក្រៅក្របខណ្ឌដើម</h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0">
                      <thead class="bg-light">
                        <tr class="small text-muted font-weight-bold">
                          <th style="width: 50px" class="text-center">ល.រ</th>
                          <th>បរិយាយ</th>
                          <th style="width: 170px" class="text-center">កាលបរិច្ឆេទចាប់ផ្តើម</th>
                          <th style="width: 170px" class="text-center">កាលបរិច្ឆេទបញ្ចប់</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(row, idx) in userStore.out_of_framework_statuses" :key="row.id" class="small">
                          <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                          <td>{{ row.description || '---' }}</td>
                          <td class="text-center">{{ formatDate(row.start_date) }}</td>
                          <td class="text-center">{{ formatDate(row.end_date) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- ង. ស្ថានភាពស្ថិតនៅក្នុងភាពទំនេរគ្មានបៀវត្ស -->
                <div v-if="userStore.employee_type === 'CIVIL_SERVICE' && userStore.unpaid_leaves?.length > 0" class="mt-4">
                  <hr class="my-3 border-light">
                  <h6 class="font-weight-bold text-success mb-3 border-left-custom pl-2" style="font-size: 14px;">ង. ស្ថានភាពស្ថិតនៅក្នុងភាពទំនេរគ្មានបៀវត្ស</h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0">
                      <thead class="bg-light">
                        <tr class="small text-muted font-weight-bold">
                          <th style="width: 50px" class="text-center">ល.រ</th>
                          <th>បរិយាយ</th>
                          <th style="width: 170px" class="text-center">កាលបរិច្ឆេទចាប់ផ្តើម</th>
                          <th style="width: 170px" class="text-center">កាលបរិច្ឆេទបញ្ចប់</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(row, idx) in userStore.unpaid_leaves" :key="row.id" class="small">
                          <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                          <td>{{ row.description || '---' }}</td>
                          <td class="text-center">{{ formatDate(row.start_date) }}</td>
                          <td class="text-center">{{ formatDate(row.end_date) }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Row 3: ៣. ប្រវត្តិការងារ (Full Width Below) -->
        <div class="row" v-if="userStore.public_work_histories?.length > 0 || userStore.private_work_histories?.length > 0">
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-lg bg-white mb-4">
              <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="font-weight-bold text-success m-0 d-flex align-items-center">
                  <div class="icon-circle bg-light-success text-success mr-2">
                    <i class="fas fa-history"></i>
                  </div>
                  ៣. ប្រវត្តិការងារ
                </h5>
              </div>
              <div class="card-body px-4 py-3">
                
                <!-- ក. ក្នុងវិស័យមុខងារសាធារណៈ -->
                <div v-if="userStore.public_work_histories?.length > 0" class="mb-4">
                  <h6 class="font-weight-bold text-success mb-3 border-left-custom pl-2" style="font-size: 14px;">ក. ក្នុងវិស័យមុខងារសាធារណៈ</h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0">
                      <thead class="bg-light">
                        <tr class="small text-muted font-weight-bold text-center">
                          <th rowspan="2" style="width: 50px" class="align-middle">ល.រ</th>
                          <th colspan="2">កាលបរិច្ឆេទបំពេញការងារ</th>
                          <th rowspan="2" class="align-middle">ក្រសួង/ស្ថាប័ន</th>
                          <th rowspan="2" class="align-middle">អង្គភាព</th>
                          <th rowspan="2" class="align-middle">មុខតំណែង</th>
                          <th rowspan="2" class="align-middle">ផ្សេងៗ</th>
                        </tr>
                        <tr class="small text-muted font-weight-bold text-center">
                          <th style="width: 110px">ចូល</th>
                          <th style="width: 110px">បញ្ចប់</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(row, idx) in userStore.public_work_histories" :key="row.id" class="small">
                          <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                          <td class="text-center">{{ formatDate(row.start_date) }}</td>
                          <td class="text-center">{{ row.end_date ? formatDate(row.end_date) : 'បច្ចុប្បន្ន' }}</td>
                          <td>{{ row.ministry || '---' }}</td>
                          <td>{{ row.unit || '---' }}</td>
                          <td>{{ row.position || '---' }}</td>
                          <td>{{ row.note || '---' }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- ខ. ក្នុងវិស័យឯកជន -->
                <div v-if="userStore.private_work_histories?.length > 0" class="mb-2">
                  <h6 class="font-weight-bold text-success mb-3 border-left-custom pl-2" style="font-size: 14px;">ខ. ក្នុងវិស័យឯកជន</h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0">
                      <thead class="bg-light">
                        <tr class="small text-muted font-weight-bold text-center">
                          <th rowspan="2" style="width: 50px" class="align-middle">ល.រ</th>
                          <th colspan="2">កាលបរិច្ឆេទបំពេញការងារ</th>
                          <th rowspan="2" class="align-middle">គ្រឹះស្ថាន/អង្គភាព</th>
                          <th rowspan="2" class="align-middle">មុខតំណែង</th>
                          <th rowspan="2" class="align-middle">ជំនាញ/បច្ចេកទេស</th>
                          <th rowspan="2" class="align-middle">ផ្សេងៗ</th>
                        </tr>
                        <tr class="small text-muted font-weight-bold text-center">
                          <th style="width: 110px">ចូល</th>
                          <th style="width: 110px">បញ្ចប់</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(row, idx) in userStore.private_work_histories" :key="row.id" class="small">
                          <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                          <td class="text-center">{{ formatDate(row.start_date) }}</td>
                          <td class="text-center">{{ row.end_date ? formatDate(row.end_date) : 'បច្ចុប្បន្ន' }}</td>
                          <td>{{ row.company || '---' }}</td>
                          <td>{{ row.position || '---' }}</td>
                          <td>{{ row.skill || '---' }}</td>
                          <td>{{ row.note || '---' }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

        <!-- Row 4: ៤. គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ ឬទណ្ឌកម្មវិន័យ (Full Width Below) -->
        <div class="row" v-if="userStore.user_decorations?.length > 0 || userStore.disciplinary_actions?.length > 0">
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-lg bg-white mb-4">
              <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="font-weight-bold text-success m-0 d-flex align-items-center">
                  <div class="icon-circle bg-light-success text-success mr-2">
                    <i class="fas fa-award"></i>
                  </div>
                  ៤. គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ ឬទណ្ឌកម្មវិន័យ
                </h5>
              </div>
              <div class="card-body px-4 py-3">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-sm mb-0">
                    <thead class="bg-light">
                      <tr class="small text-muted font-weight-bold text-center">
                        <th style="width: 50px" class="align-middle">ល.រ</th>
                        <th style="width: 180px" class="align-middle">លេខឯកសារ</th>
                        <th style="width: 140px" class="align-middle">កាលបរិច្ឆេទ</th>
                        <th class="align-middle">ស្ថាប័ន/អង្គភាព (ស្នើសុំ)</th>
                        <th class="align-middle">ខ្លឹមសារ</th>
                        <th class="align-middle">ប្រភេទ</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- ក្រុមទី ១៖ គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ -->
                      <tr class="bg-light font-weight-bold text-success">
                        <td colspan="6" class="py-2 pl-3 font-khmer font-weight-bold" style="font-size: 13px;">
                          <i class="fas fa-medal mr-1"></i> គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ
                        </td>
                      </tr>
                      <tr v-if="!userStore.user_decorations || userStore.user_decorations.length === 0" class="small">
                        <td colspan="6" class="text-center py-2 text-muted">គ្មានទិន្នន័យឡើយ</td>
                      </tr>
                      <tr v-else v-for="(row, idx) in userStore.user_decorations" :key="'dec-' + row.id" class="small">
                        <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                        <td>{{ row.document_number || '---' }}</td>
                        <td class="text-center">{{ formatDate(row.date) }}</td>
                        <td>{{ row.institution || '---' }}</td>
                        <td>{{ row.content || '---' }}</td>
                        <td>{{ row.type || '---' }}</td>
                      </tr>

                      <!-- ក្រុមទី ២៖ ទណ្ឌកម្មវិន័យ -->
                      <tr class="bg-light font-weight-bold text-danger">
                        <td colspan="6" class="py-2 pl-3 font-khmer font-weight-bold" style="font-size: 13px;">
                          <i class="fas fa-exclamation-triangle mr-1"></i> ទណ្ឌកម្មវិន័យ
                        </td>
                      </tr>
                      <tr v-if="!userStore.disciplinary_actions || userStore.disciplinary_actions.length === 0" class="small">
                        <td colspan="6" class="text-center py-2 text-muted">គ្មានទិន្នន័យឡើយ</td>
                      </tr>
                      <tr v-else v-for="(row, idx) in userStore.disciplinary_actions" :key="'disc-' + row.id" class="small">
                        <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                        <td>{{ row.document_number || '---' }}</td>
                        <td class="text-center">{{ formatDate(row.date) }}</td>
                        <td>{{ row.institution || '---' }}</td>
                        <td>{{ row.content || '---' }}</td>
                        <td>{{ row.type || '---' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Row 5: ៥. កម្រិតវប្បធម៌ទូទៅ ការបណ្តុះបណ្តាលវិជ្ជាជីវៈ និងការបណ្តុះបណ្តាលបន្ត (Full Width Below) -->
        <div class="row" v-if="userStore.educations?.length > 0">
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-lg bg-white mb-4">
              <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="font-weight-bold text-success m-0 d-flex align-items-center">
                  <div class="icon-circle bg-light-success text-success mr-2">
                    <i class="fas fa-graduation-cap"></i>
                  </div>
                  ៥. កម្រិតវប្បធម៌ទូទៅ ការបណ្តុះបណ្តាលវិជ្ជាជីវៈ និងការបណ្តុះបណ្តាលបន្ត
                </h5>
              </div>
              <div class="card-body px-4 py-3">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-sm mb-0">
                    <thead class="bg-light">
                      <tr class="small text-muted font-weight-bold text-center">
                        <th style="width: 50px" class="align-middle">ល.រ</th>
                        <th class="align-middle">កម្រិតឬវគ្គសិក្សា</th>
                        <th class="align-middle">គ្រឹះស្ថានសិក្សាបណ្តុះបណ្តាល</th>
                        <th class="align-middle">សញ្ញាបត្រដែលទទួលបាន</th>
                        <th style="width: 140px" class="align-middle">កាលបរិច្ឆេទចូលសិក្សា</th>
                        <th style="width: 140px" class="align-middle">កាលបរិច្ឆេទបញ្ចប់</th>
                        <th style="width: 130px" class="align-middle">File សញ្ញាបត្រ</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- ៥.ក កម្រិតវប្បធម៌ទូទៅ -->
                      <tr class="bg-light font-weight-bold text-success">
                        <td colspan="7" class="py-2 pl-3 font-khmer font-weight-bold" style="font-size: 13px;">
                          <i class="fas fa-school mr-1"></i> ក. កម្រិតវប្បធម៌ទូទៅ
                        </td>
                      </tr>
                      <tr v-if="educationsByCategory('GENERAL_EDUCATION').length === 0" class="small">
                        <td colspan="7" class="text-center py-2 text-muted">គ្មានទិន្នន័យឡើយ</td>
                      </tr>
                      <tr v-else v-for="(row, idx) in educationsByCategory('GENERAL_EDUCATION')" :key="'edu-gen-' + row.id" class="small">
                        <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                        <td>{{ row.course_level || '---' }}</td>
                        <td>{{ row.institution || '---' }}</td>
                        <td>{{ row.degree || '---' }}</td>
                        <td class="text-center">{{ formatDate(row.start_date) }}</td>
                        <td class="text-center">{{ row.end_date ? formatDate(row.end_date) : 'បច្ចុប្បន្ន' }}</td>
                        <td class="text-center align-middle">
                          <a v-if="row.certificate_file" :href="getFullImageUrl(row.certificate_file)" target="_blank" class="btn btn-xs btn-outline-success">
                            <i class="fas fa-eye mr-1"></i> មើលឯកសារ
                          </a>
                          <span v-else class="text-muted small">គ្មាន</span>
                        </td>
                      </tr>

                      <!-- ៥.ខ កម្រិតសញ្ញាបត្រ -->
                      <tr class="bg-light font-weight-bold text-success">
                        <td colspan="7" class="py-2 pl-3 font-khmer font-weight-bold" style="font-size: 13px;">
                          <i class="fas fa-certificate mr-1"></i> ខ. កម្រិតសញ្ញាបត្រ
                        </td>
                      </tr>
                      <tr v-if="educationsByCategory('DEGREE').length === 0" class="small">
                        <td colspan="7" class="text-center py-2 text-muted">គ្មានទិន្នន័យឡើយ</td>
                      </tr>
                      <tr v-else v-for="(row, idx) in educationsByCategory('DEGREE')" :key="'edu-deg-' + row.id" class="small">
                        <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                        <td>{{ row.course_level || '---' }}</td>
                        <td>{{ row.institution || '---' }}</td>
                        <td>{{ row.degree || '---' }}</td>
                        <td class="text-center">{{ formatDate(row.start_date) }}</td>
                        <td class="text-center">{{ row.end_date ? formatDate(row.end_date) : 'បច្ចុប្បន្ន' }}</td>
                        <td class="text-center align-middle">
                          <a v-if="row.certificate_file" :href="getFullImageUrl(row.certificate_file)" target="_blank" class="btn btn-xs btn-outline-success">
                            <i class="fas fa-eye mr-1"></i> មើលឯកសារ
                          </a>
                          <span v-else class="text-muted small">គ្មាន</span>
                        </td>
                      </tr>

                      <!-- ៥.គ ជំនាញឯកទេស -->
                      <tr class="bg-light font-weight-bold text-success">
                        <td colspan="7" class="py-2 pl-3 font-khmer font-weight-bold" style="font-size: 13px;">
                          <i class="fas fa-user-cog mr-1"></i> គ. ជំនាញឯកទេស
                        </td>
                      </tr>
                      <tr v-if="educationsByCategory('SPECIALIZATION').length === 0" class="small">
                        <td colspan="7" class="text-center py-2 text-muted">គ្មានទិន្នន័យឡើយ</td>
                      </tr>
                      <tr v-else v-for="(row, idx) in educationsByCategory('SPECIALIZATION')" :key="'edu-spec-' + row.id" class="small">
                        <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                        <td>{{ row.course_level || '---' }}</td>
                        <td>{{ row.institution || '---' }}</td>
                        <td>{{ row.degree || '---' }}</td>
                        <td class="text-center">{{ formatDate(row.start_date) }}</td>
                        <td class="text-center">{{ row.end_date ? formatDate(row.end_date) : 'បច្ចុប្បន្ន' }}</td>
                        <td class="text-center align-middle">
                          <a v-if="row.certificate_file" :href="getFullImageUrl(row.certificate_file)" target="_blank" class="btn btn-xs btn-outline-success">
                            <i class="fas fa-eye mr-1"></i> មើលឯកសារ
                          </a>
                          <span v-else class="text-muted small">គ្មាន</span>
                        </td>
                      </tr>

                      <!-- ៥.ឃ វគ្គបណ្តុះបណ្តាលក្រោម១២ខែ -->
                      <tr class="bg-light font-weight-bold text-success">
                        <td colspan="7" class="py-2 pl-3 font-khmer font-weight-bold" style="font-size: 13px;">
                          <i class="fas fa-user-graduate mr-1"></i> ឃ. វគ្គបណ្តុះបណ្តាលក្រោម១២ខែ
                        </td>
                      </tr>
                      <tr v-if="educationsByCategory('SHORT_TRAINING').length === 0" class="small">
                        <td colspan="7" class="text-center py-2 text-muted">គ្មានទិន្នន័យឡើយ</td>
                      </tr>
                      <tr v-else v-for="(row, idx) in educationsByCategory('SHORT_TRAINING')" :key="'edu-short-' + row.id" class="small">
                        <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                        <td>{{ row.course_level || '---' }}</td>
                        <td>{{ row.institution || '---' }}</td>
                        <td>{{ row.degree || '---' }}</td>
                        <td class="text-center">{{ formatDate(row.start_date) }}</td>
                        <td class="text-center">{{ row.end_date ? formatDate(row.end_date) : 'បច្ចុប្បន្ន' }}</td>
                        <td class="text-center align-middle">
                          <a v-if="row.certificate_file" :href="getFullImageUrl(row.certificate_file)" target="_blank" class="btn btn-xs btn-outline-success">
                            <i class="fas fa-eye mr-1"></i> មើលឯកសារ
                          </a>
                          <span v-else class="text-muted small">គ្មាន</span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Row 6: ៦. សមត្ថភាពភាសាបរទេស (Full Width Below) -->
        <div class="row" v-if="userStore.languages?.length > 0">
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-lg bg-white mb-4">
              <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="font-weight-bold text-success m-0 d-flex align-items-center">
                  <div class="icon-circle bg-light-success text-success mr-2">
                    <i class="fas fa-language"></i>
                  </div>
                  ៦. សមត្ថភាពភាសាបរទេស
                </h5>
              </div>
              <div class="card-body px-4 py-3">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped table-sm mb-0">
                    <thead class="bg-light">
                      <tr class="small text-muted font-weight-bold text-center">
                        <th style="width: 50px" class="align-middle">ល.រ</th>
                        <th class="align-middle">ភាសា</th>
                        <th class="align-middle">អាន</th>
                        <th class="align-middle">សរសេរ</th>
                        <th class="align-middle">និយាយ</th>
                        <th class="align-middle">ស្តាប់</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(row, idx) in userStore.languages" :key="'lang-' + row.id" class="small text-center">
                        <td class="font-weight-bold text-muted">{{ idx + 1 }}</td>
                        <td class="text-left font-weight-bold text-success pl-3">{{ row.language || '---' }}</td>
                        <td>{{ row.reading || '---' }}</td>
                        <td>{{ row.writing || '---' }}</td>
                        <td>{{ row.speaking || '---' }}</td>
                        <td>{{ row.listening || '---' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Row 7: ៧. ស្ថានភាពគ្រួសារ -->
        <div class="row">
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-lg bg-white mb-4">
              <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="font-weight-bold text-success m-0 d-flex align-items-center">
                  <div class="icon-circle bg-light-success text-success mr-2">
                    <i class="fas fa-users"></i>
                  </div>
                  ៧. ស្ថានភាពគ្រួសារ
                </h5>
              </div>
              <div class="card-body px-4 py-3 font-khmer">
                
                <!-- ក. ព័ត៌មានឪពុកម្តាយ -->
                <div class="mb-4">
                  <h6 class="text-success font-weight-bold border-left-custom pl-2 mb-3">
                    ក. ព័ត៌មានឪពុកម្តាយ
                  </h6>
                  <div class="row bg-light p-3 rounded mb-3" style="font-size: 13px;">
                    <!-- ឪពុក -->
                    <div class="col-md-6 border-right">
                      <h6 class="font-weight-bold text-success border-bottom pb-1 mb-2">ព័ត៌មានឪពុក</h6>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ឈ្មោះឪពុក៖</div>
                        <div class="col-sm-8 font-weight-bold">{{ userStore.father_name || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ជាអក្សរឡាតាំង៖</div>
                        <div class="col-sm-8 font-weight-bold text-uppercase">{{ userStore.father_latin_name || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ស្ថានភាព៖</div>
                        <div class="col-sm-8 font-weight-bold text-success">{{ userStore.father_status || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ថ្ងៃកំណើត៖</div>
                        <div class="col-sm-8">{{ userStore.father_dob ? formatDate(userStore.father_dob) : '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">សញ្ជាតិ៖</div>
                        <div class="col-sm-8">{{ userStore.father_nationality || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ទីលំនៅ៖</div>
                        <div class="col-sm-8">{{ userStore.father_address || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">មុខរបរ៖</div>
                        <div class="col-sm-8">{{ userStore.father_occupation || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ស្ថាប័ន/អង្គភាព៖</div>
                        <div class="col-sm-8">{{ userStore.father_unit || '---' }}</div>
                      </div>
                    </div>

                    <!-- ម្តាយ -->
                    <div class="col-md-6">
                      <h6 class="font-weight-bold text-success border-bottom pb-1 mb-2">ព័ត៌មានម្តាយ</h6>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ឈ្មោះម្តាយ៖</div>
                        <div class="col-sm-8 font-weight-bold">{{ userStore.mother_name || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ជាអក្សរឡាតាំង៖</div>
                        <div class="col-sm-8 font-weight-bold text-uppercase">{{ userStore.mother_latin_name || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ស្ថានភាព៖</div>
                        <div class="col-sm-8 font-weight-bold text-success">{{ userStore.mother_status || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ថ្ងៃកំណើត៖</div>
                        <div class="col-sm-8">{{ userStore.mother_dob ? formatDate(userStore.mother_dob) : '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">សញ្ជាតិ៖</div>
                        <div class="col-sm-8">{{ userStore.mother_nationality || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ទីលំនៅ៖</div>
                        <div class="col-sm-8">{{ userStore.mother_address || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">មុខរបរ៖</div>
                        <div class="col-sm-8">{{ userStore.mother_occupation || '---' }}</div>
                      </div>
                      <div class="row mb-1">
                        <div class="col-sm-4 text-muted">ស្ថាប័ន/អង្គភាព៖</div>
                        <div class="col-sm-8">{{ userStore.mother_unit || '---' }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ខ. ព័ត៌មានបងប្អូន -->
                <div class="mb-4">
                  <h6 class="text-success font-weight-bold border-left-custom pl-2 mb-3">
                    ខ. ព័ត៌មានបងប្អូន
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0">
                      <thead class="bg-light">
                        <tr class="small text-muted font-weight-bold text-center">
                          <th style="width: 50px" class="align-middle">ល.រ</th>
                          <th class="align-middle">គោត្តនាម និងនាម</th>
                          <th class="align-middle">ជាអក្សរឡាតាំង</th>
                          <th class="align-middle" style="width: 100px">ភេទ</th>
                          <th class="align-middle" style="width: 140px">ថ្ងៃខែឆ្នាំកំណើត</th>
                          <th class="align-middle">មុខរបរ (អង្គភាព)</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!userStore.siblings || userStore.siblings.length === 0" class="small">
                          <td colspan="6" class="text-center py-2 text-muted">គ្មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in userStore.siblings" :key="'sib-' + row.id" class="small">
                          <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                          <td class="font-weight-bold pl-2">{{ row.name || '---' }}</td>
                          <td class="text-uppercase pl-2">{{ row.latin_name || '---' }}</td>
                          <td class="text-center">{{ row.gender || '---' }}</td>
                          <td class="text-center">{{ row.dob ? formatDate(row.dob) : '---' }}</td>
                          <td>{{ row.occupation || '---' }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

                <!-- គ. ព័ត៌មានសហព័ទ្ធ (ប្តី ឬប្រពន្ធ) -->
                <div class="mb-4"  v-if="userStore.marital_status === 'Married'">
                  <h6 class="text-success font-weight-bold border-left-custom pl-2 mb-3">
                    គ. ព័ត៌មានសហព័ទ្ធ (ប្តី ឬប្រពន្ធ)
                  </h6>
                  <div class="bg-light p-3 rounded mb-3" style="font-size: 13px;">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="row mb-1">
                          <div class="col-sm-4 text-muted">ឈ្មោះប្តី/ប្រពន្ធ៖</div>
                          <div class="col-sm-8 font-weight-bold text-success">{{ userStore.spouse_name || '---' }}</div>
                        </div>
                        <div class="row mb-1">
                          <div class="col-sm-4 text-muted">ជាអក្សរឡាតាំង៖</div>
                          <div class="col-sm-8 font-weight-bold text-uppercase">{{ userStore.spouse_latin_name || '---' }}</div>
                        </div>
                        <div class="row mb-1">
                          <div class="col-sm-4 text-muted">ស្ថានភាព៖</div>
                          <div class="col-sm-8 font-weight-bold text-success">{{ userStore.spouse_status || '---' }}</div>
                        </div>
                        <div class="row mb-1">
                          <div class="col-sm-4 text-muted">ថ្ងៃខែឆ្នាំកំណើត៖</div>
                          <div class="col-sm-8">{{ userStore.spouse_dob ? formatDate(userStore.spouse_dob) : '---' }}</div>
                        </div>
                        <div class="row mb-1">
                          <div class="col-sm-4 text-muted">សញ្ជាតិ៖</div>
                          <div class="col-sm-8">{{ userStore.spouse_nationality || '---' }}</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="row mb-1">
                          <div class="col-sm-4 text-muted">ទីកន្លែងកំណើត៖</div>
                          <div class="col-sm-8">{{ userStore.spouse_birthplace || '---' }}</div>
                        </div>
                        <div class="row mb-1">
                          <div class="col-sm-4 text-muted">មុខរបរ៖</div>
                          <div class="col-sm-8">{{ userStore.spouse_occupation || '---' }}</div>
                        </div>
                        <div class="row mb-1">
                          <div class="col-sm-4 text-muted">ស្ថាប័ន/អង្គភាព៖</div>
                          <div class="col-sm-8">{{ userStore.spouse_unit || '---' }}</div>
                        </div>
                        <div class="row mb-1">
                          <div class="col-sm-4 text-muted">ប្រាក់ឧបត្ថម្ភ៖</div>
                          <div class="col-sm-8 font-weight-bold text-info">{{ userStore.spouse_allowance || '---' }}</div>
                        </div>
                        <div class="row mb-1">
                          <div class="col-sm-4 text-muted">លេខទូរស័ព្ទ៖</div>
                          <div class="col-sm-8">{{ userStore.spouse_phone || '---' }}</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ឃ. ព័ត៌មានកូន -->
                <div  v-if="userStore.marital_status === 'Married'">
                  <h6 class="text-success font-weight-bold border-left-custom pl-2 mb-3">
                    ឃ. ព័ត៌មានកូន
                  </h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0">
                      <thead class="bg-light">
                        <tr class="small text-muted font-weight-bold text-center">
                          <th style="width: 50px" class="align-middle">ល.រ</th>
                          <th class="align-middle">គោត្តនាម និងនាម</th>
                          <th class="align-middle">ជាអក្សរឡាតាំង</th>
                          <th class="align-middle" style="width: 100px">ភេទ</th>
                          <th class="align-middle" style="width: 140px">ថ្ងៃខែឆ្នាំកំណើត</th>
                          <th class="align-middle">មុខរបរ</th>
                          <th class="align-middle" style="width: 220px">ប្រាក់ឧបត្ថម្ភ</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="!userStore.children || userStore.children.length === 0" class="small">
                          <td colspan="7" class="text-center py-2 text-muted">គ្មានទិន្នន័យឡើយ</td>
                        </tr>
                        <tr v-else v-for="(row, idx) in userStore.children" :key="'child-' + row.id" class="small">
                          <td class="text-center font-weight-bold text-muted">{{ idx + 1 }}</td>
                          <td class="font-weight-bold pl-2">{{ row.name || '---' }}</td>
                          <td class="text-uppercase pl-2">{{ row.latin_name || '---' }}</td>
                          <td class="text-center">{{ row.gender || '---' }}</td>
                          <td class="text-center">{{ row.dob ? formatDate(row.dob) : '---' }}</td>
                          <td>{{ row.occupation || '---' }}</td>
                          <td class="font-weight-bold text-info">{{ row.allowance || '---' }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

       

      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useUserStore } from "@/stores/user";
import { apiGetMyProfile } from "@/functions/api/user";
import emptyImage from "@/assets/images/emptyImage.png";

const userStore = useUserStore();

onMounted(async () => {
  try {
    const response = await apiGetMyProfile();
    const userData = response.data.user || response.data.data || response.data;
    if (userData && typeof userStore.setState === 'function') {
      userStore.setState(userData);
    }
  } catch (error) {
    console.error("មិនអាចទាញយកទិន្នន័យ Profile បានឡើយ:", error);
  }
});

const toKhmerNum = (num) => {
  const khmerDigits = ['០', '១', '២', '៣', '៤', '៥', '៦', '៧', '៨', '៩'];
  return String(num).split('').map(char => {
    if (char >= '0' && char <= '9') {
      return khmerDigits[parseInt(char)];
    }
    return char;
  }).join('');
};

const formatDate = (dateString) => {
  if (!dateString) return '---';
  const cleanDate = String(dateString).split('T')[0];
  const parts = cleanDate.split('-');
  if (parts.length !== 3) return cleanDate;
  
  const year = parts[0];
  const month = parts[1];
  const day = parseInt(parts[2]);
  
  const khmerMonths = {
    '01': 'មករា',
    '02': 'កុម្ភៈ',
    '03': 'មីនា',
    '04': 'មេសា',
    '05': 'ឧសភា',
    '06': 'មិថុនា',
    '07': 'កក្កដា',
    '08': 'សីហា',
    '09': 'កញ្ញា',
    '10': 'តុលា',
    '11': 'វិច្ឆិកា',
    '12': 'ធ្នូ'
  };

  const khDay = toKhmerNum(day);
  const khMonth = khmerMonths[month] || month;
  const khYear = toKhmerNum(year);

  return `${khDay} ${khMonth} ${khYear}`;
};
const formatGender = (gender) => {
  if (!gender) return '---';
  const g = String(gender).trim().toUpperCase();
  return (g === 'MALE' || g === 'M') ? 'ប្រុស' : (g === 'FEMALE' || g === 'F') ? 'ស្រី' : gender;
};
const formatMaritalStatus = (status) => {
  if (!status) return '---';
  const s = String(status).trim().toUpperCase();
  return s === 'SINGLE' ? 'នៅលីវ' : s === 'MARRIED' ? 'រៀបការរួច' : s === 'DIVORCED' ? 'លែងលះ' : status;
};

const educationsByCategory = (category) => {
  return (userStore.educations || []).filter(e => e.category === category);
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
  const backendBase = (import.meta.env.VITE_APP_API_URL || 'http://localhost:8000')
    .replace(/\/api\/?$/, '');
  let cleanPath = path.replace(/^\//, '');
  if (!cleanPath.startsWith('storage/') && !cleanPath.startsWith('uploads/')) {
    cleanPath = `storage/${cleanPath}`;
  }
  return `${backendBase}/${cleanPath}`;
};

const getFormattedPrintDateTime = () => {
  const now = new Date();
  const d = String(now.getDate()).padStart(2, '0');
  const m = String(now.getMonth() + 1).padStart(2, '0');
  const y = now.getFullYear();
  const h = String(now.getHours()).padStart(2, '0');
  const min = String(now.getMinutes()).padStart(2, '0');
  const s = String(now.getSeconds()).padStart(2, '0');
  return `${d}-${m}-${y} ${h}:${min}:${s}`;
};

const getQrCodeData = () => {
  return encodeURIComponent(window.location.origin + '/users/profile/' + (userStore.id || ''));
};

const formatOfficerStatus = (status) => {
  const map = {
    ACTIVE: 'កំពុងបម្រើការងារ',
    RESIGNED: 'លាឈប់',
    RETIRED: 'ចូលនិវត្តន៍',
    TRANSFERRED: 'ផ្លាស់ប្តូរ',
    SUSPENDED: 'ព្យួរការងារ',
    OTHER: 'ផ្សេងៗ',
  };
  return map[status] || 'កំពុងបម្រើការងារ';
};

const getOfficerStatusBadgeClass = (status) => {
  const map = {
    ACTIVE: 'badge badge-success px-2 py-1',
    RESIGNED: 'badge badge-danger px-2 py-1',
    RETIRED: 'badge badge-secondary px-2 py-1',
    TRANSFERRED: 'badge badge-info px-2 py-1',
    SUSPENDED: 'badge badge-warning text-dark px-2 py-1',
    OTHER: 'badge badge-dark px-2 py-1',
  };
  return map[status] || 'badge badge-success px-2 py-1';
};

const calculateServiceDuration = (startDate, endDate) => {
  if (!startDate) return null;
  try {
    const start = new Date(startDate);
    const end = endDate ? new Date(endDate) : new Date();
    if (isNaN(start.getTime()) || isNaN(end.getTime()) || end < start) return '០ ថ្ងៃ';

    let years = end.getFullYear() - start.getFullYear();
    let months = end.getMonth() - start.getMonth();
    let days = end.getDate() - start.getDate();

    if (days < 0) {
      months--;
      const prevMonth = new Date(end.getFullYear(), end.getMonth(), 0);
      days += prevMonth.getDate();
    }
    if (months < 0) {
      years--;
      months += 12;
    }

    const parts = [];
    if (years > 0) parts.push(`${years} ឆ្នាំ`);
    if (months > 0) parts.push(`${months} ខែ`);
    if (years === 0 && months === 0) {
      parts.push(days > 0 ? `${days} ថ្ងៃ` : 'ទើបចូលបម្រើការងារ');
    }
    return parts.join(' ');
  } catch (e) {
    return null;
  }
};
</script>

<style scoped>
.content-wrapper {
  font-family: 'Battambang', sans-serif !important;
}

.profile-cover-banner {
  background: linear-gradient(135deg, #112d26 0%, #1e4d41 100%);
  height: 120px;
  border-radius: 0 0 15px 15px;
}

.rounded-lg {
  border-radius: 14px !important;
}

.icon-circle {
  width: 32px;
  height: 32px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  font-size: 14px;
}

.bg-light-success {
  background-color: rgba(40, 167, 69, 0.1) !important;
}

.border-left-custom {
  border-left: 4px solid #28a745;
}

.font-lg {
  font-size: 16px;
}

/* 🟢 កែសម្រួលកាតខាងឆ្វេងឱ្យទូលាយល្មម មិនបាច់បុកគ្នាជាមួយអក្សរវែង */
.profile-card-left {
  word-break: break-word;
  overflow: hidden;
}

/* 🟢 ការពារអក្សរក្នុង text-truncate មិនឱ្យដាច់ ព្រមទាំងផ្តល់កន្លែងគ្រប់គ្រាន់ */
.text-truncate-custom {
  white-space: normal !important;
  word-break: break-word;
}
</style>
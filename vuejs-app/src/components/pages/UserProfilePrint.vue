<template>
  <div class="content-wrapper print-page" style="background-color: #f4f6f9; min-height: 1000px;">
    <!-- Action buttons (លាក់ពេលព្រីនចេញ) -->
    <section class="content-header print-hide">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark font-weight-bold page-title">បោះពុម្ពប្រវត្តិរូបមន្ត្រី</h1>
          </div>
          <div class="col-sm-6 text-right">
            <button @click="printProfile" class="btn btn-primary px-4 shadow-sm">
              <i class="fas fa-print mr-1"></i> បោះពុម្ព (Print A4)
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Printable A4 Area -->
    <section class="content">
      <div class="container-fluid d-flex justify-content-center">
        <div class="a4-sheet bg-white p-5 shadow-sm">
          <!-- Header Section -->
          <div class="row align-items-start mb-2">
            <div class="col-3 text-center">
              <img :src="ministryLogo" alt="Logo" class="ministry-logo mb-1" />
              <div class="tr-title">និយ័តករបរធនបាលកិច្ច</div>
              <div class="ga-title">នាយកដ្ឋានកិច្ចការទូទៅ</div>
            </div>
            <div class="col-6 text-center" style="top: -20px;">
              <div class="kingdom-title">ព្រះរាជាណាចក្រកម្ពុជា</div>
              <div class="kingdom-title">ជាតិ សាសនា ព្រះមហាក្សត្រ</div>
            </div>
            <div class="col-3 text-center">
              <!-- Profile Photo -->
              <img :src="userProfile.profile_image || emptyImage" alt="Profile" class="profile-photo border" />
            </div>
          </div>

          <!-- Title Section -->
          <div class="text-center mb-3">
            <h4 class="main-doc-title">ប្រវត្តិរូបសង្ខេប</h4>
          </div>

          <!-- Section 1: Personal Info -->
          <div class="section-title mb-3">១. ព័ត៌មានផ្ទាល់ខ្លួន</div>

          <div class="profile-info-grid">
            <div class="row mb-2" v-if="userProfile?.employee_type === 'CIVIL_SERVICE'">
              <div class="col-4"><span class="label-title">អត្តលេខមន្ត្រីរាជការ</span>: <span class="label-value">{{ userProfile.employee_code || '---' }}</span></div>
              <div class="col-4"><span class="label-title">លេខប័ណ្ណសម្គាល់មន្ត្រី</span>: <span class="label-value">{{ userProfile.mef_card_number || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-6"><span class="label-title">គោត្តនាម និងនាម</span>: <span class="label-value">{{ userProfile.name_kh || userProfile.name || '---' }}</span></div>
              <div class="col-6"><span class="label-title">អក្សរឡាតាំង</span>: <span class="label-value text-uppercase">{{ userProfile.name_en || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-4"><span class="label-title">ភេទ</span>: <span class="label-value">{{ formatGender(userProfile.gender) }}</span></div>
              <div class="col-4"><span class="label-title">ថ្ងៃខែឆ្នាំកំណើត</span>: <span class="label-value">{{ formatDate(userProfile.dob) }}</span></div>
              <div class="col-4"><span class="label-title">ស្ថានភាពគ្រួសារ</span>: <span class="label-value">{{ formatMaritalStatus(userProfile.marital_status) }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-12"><span class="label-title">ទីកន្លែងកំណើត</span>: <span class="label-value">{{ userProfile.birth_place || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-12"><span class="label-title">អាសយដ្ឋានបច្ចុប្បន្ន</span>: <span class="label-value">{{ userProfile.current_address || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-12"><span class="label-title">អ៊ីមែល</span>: <span class="label-value">{{ userProfile.email || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-12"><span class="label-title">លេខទូរសព្ទ</span>: <span class="label-value">{{ userProfile.phone || '---' }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-6"><span class="label-title">អត្តសញ្ញាណប័ណ្ណ</span>: <span class="label-value">{{ userProfile.national_id_number || '---' }}</span></div>
              <div class="col-6"><span class="label-title">កាលបរិច្ឆេទផុតកំណត់</span>: <span class="label-value">{{ formatDate(userProfile.national_id_expired_date) }}</span></div>
            </div>

            <div class="row mb-2">
              <div class="col-6"><span class="label-title">លិខិតឆ្លងដែន</span>: <span class="label-value">{{ userProfile.passport_number || '---' }}</span></div>
              <div class="col-6"><span class="label-title">កាលបរិច្ឆេទផុតកំណត់</span>: <span class="label-value">{{ formatDate(userProfile.passport_expired_date) }}</span></div>
            </div>
          </div>

          <!-- Section 2: Career / Function Status -->
          <div class="section-title">២. ព័ត៌មានអំពីស្ថានភាពមុខងារ</div>
          <div class="section-title mb-3 ml-3">ក. ចូលបម្រើការងារដំបូង</div>
          
          <div class="row mb-2">
            <div class="col-3"><span class="label-title">កាលបរិច្ឆេទចូលបម្រើការងារដំបូង</span></div>
            <div class="col-3"> : <span class="label-value">{{ formatDate(userProfile.first_service_date) }}</span></div>
            <div class="col-2"><span class="label-title">កាលបរិច្ឆេទតាំងស៊ុប់</span></div>
            <div class="col-3"> : <span class="label-value">{{ formatDate(userProfile.first_appointment_date) }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-3"><span class="label-title">ក្របខណ្ឌ ឋានន្តរស័ក្តិ និងថ្នាក់</span></div>
            <div class="col-3">: <span class="label-value text-uppercase">{{ userProfile.initial_framework || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-3"><span class="label-title">មុខតំណែង</span></div>
            <div class="col-3">: <span class="label-value text-uppercase">{{ userProfile.initial_position || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-3"><span class="label-title">ក្រសួង/ស្ថាប័ន</span></div>
            <div class="col-9">: <span class="label-value text-uppercase">{{ userProfile.initial_ministry || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-3"><span class="label-title">អង្គភាព</span></div>
            <div class="col-9">: <span class="label-value text-uppercase">{{ userProfile.initial_unit || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-3"><span class="label-title">នាយកដ្ឋាន/អង្គភាព/មន្ទីរ</span></div>
            <div class="col-3">: <span class="label-value text-uppercase">{{ userProfile.initial_department || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-3"><span class="label-title">ការិយាល័យ</span></div>
            <div class="col-3">: <span class="label-value text-uppercase">{{ userProfile.initial_office || '---' }}</span></div>
          </div>

          <div class="section-title mb-3 ml-3">ខ. ស្ថានភាពមុខងារបច្ចុប្បន្ន</div>
          
          <div class="row mb-2">
            <div class="col-3"><span class="label-title">ក្របខណ្ឌ ឋានន្តរស័ក្តិ និងថ្នាក់</span></div>
            <div class="col-2"> : <span class="label-value">{{ userProfile.current_framework || '---' }}</span></div>
            <div class="col-5"><span class="label-title">កាលបរិច្ឆេទប្តូរក្របខណ្ឌ ឋានន្តរស័ក្តិ និងថ្នាក់ចុងក្រោយ</span></div>
            <div class="col-2"> : <span class="label-value">{{ formatDate(userProfile.current_appointment_date) }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-4"><span class="label-title">មុខតំណែង</span></div>
            <div class="col-3">: <span class="label-value text-uppercase">{{ userProfile.position?.title_kh || userProfile.position?.name || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-4"><span class="label-title">កាលបរិច្ឆេទទទួលមុខតំណែងចុងក្រោយ</span></div>
            <div class="col-3">: <span class="label-value text-uppercase">{{ formatDate(userProfile.current_position_date) }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-4"><span class="label-title">នាយកដ្ឋាន</span></div>
            <div class="col-3">: <span class="label-value text-uppercase">{{ userProfile.department?.name_kh || userProfile.department?.name || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-4"><span class="label-title">ការិយាល័យ</span></div>
            <div class="col-3">: <span class="label-value text-uppercase">{{ userProfile.office?.name_kh || userProfile.office?.name || '---' }}</span></div>
          </div>

          <div class="row mb-2">
            <div class="col-4"><span class="label-title">ស្ថានភាពមន្ត្រី</span></div>
            <div class="col-3">: <span class="label-value font-weight-bold">{{ formatOfficerStatus(userProfile.officer_status) }}</span></div>
            <div class="col-5" v-if="userProfile.officer_status_date && userProfile.officer_status !== 'ACTIVE'">
              <span class="label-title">កាលបរិច្ឆេទ</span>: <span class="label-value">{{ formatDate(userProfile.officer_status_date) }}</span>
            </div>
          </div>

          <div class="row mb-2">
            <div class="col-4"><span class="label-title">អតីតភាពការងារ (រយៈពេលបម្រើការងារ)</span></div>
            <div class="col-8">: <span class="label-value font-weight-bold">{{ userProfile.service_duration_formatted || calculateServiceDuration(userProfile.first_service_date || userProfile.first_appointment_date, userProfile.officer_status !== 'ACTIVE' ? userProfile.officer_status_date : null) || '---' }}</span></div>
          </div>

          <!-- គ. តួនាទីបន្ថែមលើមុខងារបច្ចុប្បន្ន -->
          <div v-if="userProfile.employee_type === 'CIVIL_SERVICE'" class="mt-3">
            <div class="section-title mb-2 ml-3">គ. តួនាទីបន្ថែមលើមុខងារបច្ចុប្បន្ន</div>
            <div class="ml-3 table-responsive">
              <table class="table table-bordered table-sm print-table mb-3">
                <thead class="bg-light">
                  <tr class="font-weight-bold text-center" style="font-size: 11px;">
                    <th style="width: 40px">ល.រ</th>
                    <th style="width: 100px">កាលបរិច្ឆេទ</th>
                    <th>ឯកសារយោង</th>
                    <th>មុខតំណែង</th>
                    <th>ឋានៈស្មើ</th>
                    <th>អង្គភាព</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!userProfile.additional_positions || userProfile.additional_positions.length === 0">
                    <td colspan="6" class="text-center py-1 text-muted" style="font-size: 11px;">មិនទាន់មានទិន្នន័យឡើយ</td>
                  </tr>
                  <tr v-else v-for="(row, idx) in userProfile.additional_positions" :key="row.id" style="font-size: 11px;">
                    <td class="text-center">{{ idx + 1 }}</td>
                    <td class="text-center">{{ formatDate(row.date) }}</td>
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
          <div v-if="userProfile.employee_type === 'CIVIL_SERVICE'" class="mt-3">
            <div class="section-title mb-2 ml-3">ឃ. ស្ថានភាពស្ថិតនៅក្រៅក្របខណ្ឌដើម</div>
            <div class="ml-3 table-responsive">
              <table class="table table-bordered table-sm print-table mb-3">
                <thead class="bg-light">
                  <tr class="font-weight-bold text-center" style="font-size: 11px;">
                    <th style="width: 40px">ល.រ</th>
                    <th>បរិយាយ</th>
                    <th style="width: 130px">កាលបរិច្ឆេទចាប់ផ្តើម</th>
                    <th style="width: 130px">កាលបរិច្ឆេទបញ្ចប់</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!userProfile.out_of_framework_statuses || userProfile.out_of_framework_statuses.length === 0">
                    <td colspan="4" class="text-center py-1 text-muted" style="font-size: 11px;">មិនទាន់មានទិន្នន័យឡើយ</td>
                  </tr>
                  <tr v-else v-for="(row, idx) in userProfile.out_of_framework_statuses" :key="row.id" style="font-size: 11px;">
                    <td class="text-center">{{ idx + 1 }}</td>
                    <td>{{ row.description || '---' }}</td>
                    <td class="text-center">{{ formatDate(row.start_date) }}</td>
                    <td class="text-center">{{ formatDate(row.end_date) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ង. ស្ថានភាពស្ថិតនៅក្នុងភាពទំនេរគ្មានបៀវត្ស -->
          <div v-if="userProfile.employee_type === 'CIVIL_SERVICE'" class="mt-3">
            <div class="section-title mb-2 ml-3">ង. ស្ថានភាពស្ថិតនៅក្នុងភាពទំនេរគ្មានបៀវត្ស</div>
            <div class="ml-3 table-responsive">
              <table class="table table-bordered table-sm print-table mb-3">
                <thead class="bg-light">
                  <tr class="font-weight-bold text-center" style="font-size: 11px;">
                    <th style="width: 40px">ល.រ</th>
                    <th>បរិយាយ</th>
                    <th style="width: 130px">កាលបរិច្ឆេទចាប់ផ្តើម</th>
                    <th style="width: 130px">កាលបរិច្ឆេទបញ្ចប់</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!userProfile.unpaid_leaves || userProfile.unpaid_leaves.length === 0">
                    <td colspan="4" class="text-center py-1 text-muted" style="font-size: 11px;">មិនទាន់មានទិន្នន័យឡើយ</td>
                  </tr>
                  <tr v-else v-for="(row, idx) in userProfile.unpaid_leaves" :key="row.id" style="font-size: 11px;">
                    <td class="text-center">{{ idx + 1 }}</td>
                    <td>{{ row.description || '---' }}</td>
                    <td class="text-center">{{ formatDate(row.start_date) }}</td>
                    <td class="text-center">{{ formatDate(row.end_date) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ៣. ប្រវត្តិការងារ -->
          <div class="mt-3">
            <div class="section-title mb-2 ml-3">៣. ប្រវត្តិការងារ</div>
            
            <!-- ក. ក្នុងវិស័យមុខងារសាធារណៈ -->
            <div class="ml-3 mt-2">
              <div class="font-weight-bold mb-1" style="font-size: 11px;">ក. ក្នុងវិស័យមុខងារសាធារណៈ</div>
              <div class="table-responsive">
                <table class="table table-bordered table-sm print-table mb-3">
                  <thead class="bg-light">
                    <tr class="font-weight-bold text-center align-middle" style="font-size: 11px;">
                      <th rowspan="2" style="width: 40px" class="align-middle">ល.រ</th>
                      <th colspan="2">កាលបរិច្ឆេទបំពេញការងារ</th>
                      <th rowspan="2" class="align-middle">ក្រសួង/ស្ថាប័ន</th>
                      <th rowspan="2" class="align-middle">អង្គភាព</th>
                      <th rowspan="2" class="align-middle">មុខតំណែង</th>
                      <th rowspan="2" class="align-middle">ផ្សេងៗ</th>
                    </tr>
                    <tr class="font-weight-bold text-center" style="font-size: 11px;">
                      <th style="width: 90px">ចូល</th>
                      <th style="width: 90px">បញ្ចប់</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="!userProfile.public_work_histories || userProfile.public_work_histories.length === 0">
                      <td colspan="7" class="text-center py-1 text-muted" style="font-size: 11px;">មិនទាន់មានទិន្នន័យឡើយ</td>
                    </tr>
                    <tr v-else v-for="(row, idx) in userProfile.public_work_histories" :key="row.id" style="font-size: 11px;">
                      <td class="text-center">{{ idx + 1 }}</td>
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
            <div class="ml-3 mt-2">
              <div class="font-weight-bold mb-1" style="font-size: 11px;">ខ. ក្នុងវិស័យឯកជន</div>
              <div class="table-responsive">
                <table class="table table-bordered table-sm print-table mb-3">
                  <thead class="bg-light">
                    <tr class="font-weight-bold text-center align-middle" style="font-size: 11px;">
                      <th rowspan="2" style="width: 40px" class="align-middle">ល.រ</th>
                      <th colspan="2">កាលបរិច្ឆេទបំពេញការងារ</th>
                      <th rowspan="2" class="align-middle">គ្រឹះស្ថាន/អង្គភាព</th>
                      <th rowspan="2" class="align-middle">មុខតំណែង</th>
                      <th rowspan="2" class="align-middle">ជំនាញ/បច្ចេកទេស</th>
                      <th rowspan="2" class="align-middle">ផ្សេងៗ</th>
                    </tr>
                    <tr class="font-weight-bold text-center" style="font-size: 11px;">
                      <th style="width: 90px">ចូល</th>
                      <th style="width: 90px">បញ្ចប់</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="!userProfile.private_work_histories || userProfile.private_work_histories.length === 0">
                      <td colspan="7" class="text-center py-1 text-muted" style="font-size: 11px;">មិនទាន់មានទិន្នន័យឡើយ</td>
                    </tr>
                    <tr v-else v-for="(row, idx) in userProfile.private_work_histories" :key="row.id" style="font-size: 11px;">
                      <td class="text-center">{{ idx + 1 }}</td>
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

          <!-- ៤. គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ ឬទណ្ឌកម្មវិន័យ -->
          <div class="mt-3">
            <div class="section-title mb-2 ml-3">៤. គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ ឬទណ្ឌកម្មវិន័យ</div>
            <div class="ml-3 mt-2">
              <div class="table-responsive">
                <table class="table table-bordered table-sm print-table mb-3">
                  <thead class="bg-light">
                    <tr class="font-weight-bold text-center" style="font-size: 11px;">
                      <th style="width: 40px">ល.រ</th>
                      <th style="width: 140px">លេខឯកសារ</th>
                      <th style="width: 110px">កាលបរិច្ឆេទ</th>
                      <th>ស្ថាប័ន/អង្គភាព (ស្នើសុំ)</th>
                      <th>ខ្លឹមសារ</th>
                      <th>ប្រភេទ</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ -->
                    <tr class="bg-light font-weight-bold text-dark" style="font-size: 11px;">
                      <td colspan="6" class="py-1 pl-2">គ្រឿងឥស្សរិយយស ប័ណ្ណសរសើរ</td>
                    </tr>
                    <tr v-if="!userProfile.user_decorations || userProfile.user_decorations.length === 0" style="font-size: 11px;">
                      <td colspan="6" class="text-center py-1 text-muted">គ្មានទិន្នន័យឡើយ</td>
                    </tr>
                    <tr v-else v-for="(row, idx) in userProfile.user_decorations" :key="'dec-' + row.id" style="font-size: 11px;">
                      <td class="text-center">{{ idx + 1 }}</td>
                      <td>{{ row.document_number || '---' }}</td>
                      <td class="text-center">{{ formatDate(row.date) }}</td>
                      <td>{{ row.institution || '---' }}</td>
                      <td>{{ row.content || '---' }}</td>
                      <td>{{ row.type || '---' }}</td>
                    </tr>

                    <!-- ទណ្ឌកម្មវិន័យ -->
                    <tr class="bg-light font-weight-bold text-dark" style="font-size: 11px;">
                      <td colspan="6" class="py-1 pl-2">ទណ្ឌកម្មវិន័យ</td>
                    </tr>
                    <tr v-if="!userProfile.disciplinary_actions || userProfile.disciplinary_actions.length === 0" style="font-size: 11px;">
                      <td colspan="6" class="text-center py-1 text-muted">គ្មានទិន្នន័យឡើយ</td>
                    </tr>
                    <tr v-else v-for="(row, idx) in userProfile.disciplinary_actions" :key="'disc-' + row.id" style="font-size: 11px;">
                      <td class="text-center">{{ idx + 1 }}</td>
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

          <!-- ៥. កម្រិតវប្បធម៌ទូទៅ ការបណ្តុះបណ្តាលវិជ្ជាជីវៈ និងការបណ្តុះបណ្តាលបន្ត -->
          <div class="mt-3" v-if="userProfile.educations?.length > 0">
            <div class="section-title mb-2 ml-3">៥. កម្រិតវប្បធម៌ទូទៅ ការបណ្តុះបណ្តាលវិជ្ជាជីវៈ និងការបណ្តុះបណ្តាលបន្ត</div>
            <div class="ml-3 mt-2">
              <div class="table-responsive">
                <table class="table table-bordered table-sm print-table mb-3">
                  <thead class="bg-light">
                    <tr class="font-weight-bold text-center" style="font-size: 11px;">
                      <th style="width: 40px">ល.រ</th>
                      <th>កម្រិតឬវគ្គសិក្សា</th>
                      <th>គ្រឹះស្ថានសិក្សាបណ្តុះបណ្តាល</th>
                      <th>សញ្ញាបត្រដែលទទួលបាន</th>
                      <th style="width: 110px">ថ្ងៃចូលសិក្សា</th>
                      <th style="width: 110px">ថ្ងៃបញ្ចប់</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- កម្រិតវប្បធម៌ទូទៅ -->
                    <tr class="bg-light font-weight-bold text-dark" style="font-size: 11px;">
                      <td colspan="6" class="py-1 pl-2">ក. កម្រិតវប្បធម៌ទូទៅ</td>
                    </tr>
                    <tr v-if="educationsByCategory('GENERAL_EDUCATION').length === 0" style="font-size: 11px;">
                      <td colspan="6" class="text-center py-1 text-muted">គ្មានទិន្នន័យឡើយ</td>
                    </tr>
                    <tr v-else v-for="(row, idx) in educationsByCategory('GENERAL_EDUCATION')" :key="'edu-gen-' + row.id" style="font-size: 11px;">
                      <td class="text-center">{{ idx + 1 }}</td>
                      <td>{{ row.course_level || '---' }}</td>
                      <td>{{ row.institution || '---' }}</td>
                      <td>{{ row.degree || '---' }}</td>
                      <td class="text-center">{{ formatDate(row.start_date) }}</td>
                      <td class="text-center">{{ row.end_date ? formatDate(row.end_date) : 'បច្ចុប្បន្ន' }}</td>
                    </tr>

                    <!-- កម្រិតសញ្ញាបត្រ -->
                    <tr class="bg-light font-weight-bold text-dark" style="font-size: 11px;">
                      <td colspan="6" class="py-1 pl-2">ខ. កម្រិតសញ្ញាបត្រ</td>
                    </tr>
                    <tr v-if="educationsByCategory('DEGREE').length === 0" style="font-size: 11px;">
                      <td colspan="6" class="text-center py-1 text-muted">គ្មានទិន្នន័យឡើយ</td>
                    </tr>
                    <tr v-else v-for="(row, idx) in educationsByCategory('DEGREE')" :key="'edu-deg-' + row.id" style="font-size: 11px;">
                      <td class="text-center">{{ idx + 1 }}</td>
                      <td>{{ row.course_level || '---' }}</td>
                      <td>{{ row.institution || '---' }}</td>
                      <td>{{ row.degree || '---' }}</td>
                      <td class="text-center">{{ formatDate(row.start_date) }}</td>
                      <td class="text-center">{{ row.end_date ? formatDate(row.end_date) : 'បច្ចុប្បន្ន' }}</td>
                    </tr>

                    <!-- ជំនាញឯកទេស -->
                    <tr class="bg-light font-weight-bold text-dark" style="font-size: 11px;">
                      <td colspan="6" class="py-1 pl-2">គ. ជំនាញឯកទេស</td>
                    </tr>
                    <tr v-if="educationsByCategory('SPECIALIZATION').length === 0" style="font-size: 11px;">
                      <td colspan="6" class="text-center py-1 text-muted">គ្មានទិន្នន័យឡើយ</td>
                    </tr>
                    <tr v-else v-for="(row, idx) in educationsByCategory('SPECIALIZATION')" :key="'edu-spec-' + row.id" style="font-size: 11px;">
                      <td class="text-center">{{ idx + 1 }}</td>
                      <td>{{ row.course_level || '---' }}</td>
                      <td>{{ row.institution || '---' }}</td>
                      <td>{{ row.degree || '---' }}</td>
                      <td class="text-center">{{ formatDate(row.start_date) }}</td>
                      <td class="text-center">{{ row.end_date ? formatDate(row.end_date) : 'បច្ចុប្បន្ន' }}</td>
                    </tr>

                    <!-- វគ្គបណ្តុះបណ្តាលក្រោម១២ខែ -->
                    <tr class="bg-light font-weight-bold text-dark" style="font-size: 11px;">
                      <td colspan="6" class="py-1 pl-2">ឃ. វគ្គបណ្តុះបណ្តាលក្រោម១២ខែ</td>
                    </tr>
                    <tr v-if="educationsByCategory('SHORT_TRAINING').length === 0" style="font-size: 11px;">
                      <td colspan="6" class="text-center py-1 text-muted">គ្មានទិន្នន័យឡើយ</td>
                    </tr>
                    <tr v-else v-for="(row, idx) in educationsByCategory('SHORT_TRAINING')" :key="'edu-short-' + row.id" style="font-size: 11px;">
                      <td class="text-center">{{ idx + 1 }}</td>
                      <td>{{ row.course_level || '---' }}</td>
                      <td>{{ row.institution || '---' }}</td>
                      <td>{{ row.degree || '---' }}</td>
                      <td class="text-center">{{ formatDate(row.start_date) }}</td>
                      <td class="text-center">{{ row.end_date ? formatDate(row.end_date) : 'បច្ចុប្បន្ន' }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- ៦. សមត្ថភាពភាសាបរទេស -->
          <div class="mt-3" v-if="userProfile.languages?.length > 0">
            <div class="section-title mb-2 ml-3">៦. សមត្ថភាពភាសាបរទេស</div>
            <div class="ml-3 mt-2">
              <div class="table-responsive">
                <table class="table table-bordered table-sm print-table mb-3">
                  <thead class="bg-light text-center">
                    <tr class="font-weight-bold text-center" style="font-size: 11px;">
                      <th style="width: 40px">ល.រ</th>
                      <th style="width: 200px">ភាសា</th>
                      <th>អាន</th>
                      <th>សរសេរ</th>
                      <th>និយាយ</th>
                      <th>ស្តាប់</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(row, idx) in userProfile.languages" :key="'lang-' + row.id" style="font-size: 11px;" class="text-center">
                      <td>{{ idx + 1 }}</td>
                      <td class="text-left font-weight-bold pl-2">{{ row.language || '---' }}</td>
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

          <!-- ៧. ស្ថានភាពគ្រួសារ -->
          <div class="mt-3">
            <div class="section-title mb-2 ml-3">៧. ស្ថានភាពគ្រួសារ</div>
            <div class="ml-3 mt-2 font-khmer" style="font-size: 11px;">
              
              <!-- ក. ព័ត៌មានឪពុកម្តាយ -->
              <div class="font-weight-bold text-success mb-1" style="font-size: 11.5px;">ក. ព័ត៌មានឪពុកម្តាយ</div>
              
              <!-- ឪពុក -->
              <div class="pl-2 mb-2">
                <div class="row">
                  <div class="col-6">ឈ្មោះឪពុក៖ <span class="font-weight-bold">{{ userProfile.father_name || '---' }}</span></div>
                  <div class="col-6">ជាអក្សរឡាតាំង៖ <span class="font-weight-bold text-uppercase">{{ userProfile.father_latin_name || '---' }}</span></div>
                </div>
                <div class="row mt-1">
                  <div class="col-4">ថ្ងៃកំណើត៖ <span>{{ userProfile.father_dob ? formatDate(userProfile.father_dob) : '---' }}</span></div>
                  <div class="col-4">សញ្ជាតិ៖ <span>{{ userProfile.father_nationality || '---' }}</span></div>
                  <div class="col-4">ស្ថានភាព៖ <span class="font-weight-bold">{{ userProfile.father_status || '---' }}</span></div>
                </div>
                <div class="row mt-1">
                  <div class="col-12">ទីលំនៅបច្ចុប្បន្ន៖ <span>{{ userProfile.father_address || '---' }}</span></div>
                </div>
                <div class="row mt-1">
                  <div class="col-6">មុខរបរ៖ <span>{{ userProfile.father_occupation || '---' }}</span></div>
                  <div class="col-6">ស្ថាប័ន/អង្គភាព៖ <span>{{ userProfile.father_unit || '---' }}</span></div>
                </div>
              </div>

              <!-- ម្តាយ -->
              <div class="pl-2 mb-3 border-top pt-2">
                <div class="row">
                  <div class="col-6">ឈ្មោះម្តាយ៖ <span class="font-weight-bold">{{ userProfile.mother_name || '---' }}</span></div>
                  <div class="col-6">ជាអក្សរឡាតាំង៖ <span class="font-weight-bold text-uppercase">{{ userProfile.mother_latin_name || '---' }}</span></div>
                </div>
                <div class="row mt-1">
                  <div class="col-4">ថ្ងៃកំណើត៖ <span>{{ userProfile.mother_dob ? formatDate(userProfile.mother_dob) : '---' }}</span></div>
                  <div class="col-4">សញ្ជាតិ៖ <span>{{ userProfile.mother_nationality || '---' }}</span></div>
                  <div class="col-4">ស្ថានភាព៖ <span class="font-weight-bold">{{ userProfile.mother_status || '---' }}</span></div>
                </div>
                <div class="row mt-1">
                  <div class="col-12">ទីលំនៅបច្ចុប្បន្ន៖ <span>{{ userProfile.mother_address || '---' }}</span></div>
                </div>
                <div class="row mt-1">
                  <div class="col-6">មុខរបរ៖ <span>{{ userProfile.mother_occupation || '---' }}</span></div>
                  <div class="col-6">ស្ថាប័ន/អង្គភាព៖ <span>{{ userProfile.mother_unit || '---' }}</span></div>
                </div>
              </div>

              <!-- ខ. ព័ត៌មានបងប្អូន -->
              <div v-if="userProfile.siblings?.length > 0" class="mb-3">
                <div class="font-weight-bold text-success mb-2" style="font-size: 11.5px;">ខ. ព័ត៌មានបងប្អូន</div>
                <div class="table-responsive">
                  <table class="table table-bordered table-sm print-table mb-2">
                    <thead class="bg-light text-center">
                      <tr class="font-weight-bold" style="font-size: 10.5px;">
                        <th style="width: 40px">ល.រ</th>
                        <th>គោត្តនាម និងនាម</th>
                        <th>ជាអក្សរឡាតាំង</th>
                        <th style="width: 80px">ភេទ</th>
                        <th style="width: 120px">ថ្ងៃខែឆ្នាំកំណើត</th>
                        <th>មុខរបរ (អង្គភាព)</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(row, idx) in userProfile.siblings" :key="'print-sib-' + row.id" style="font-size: 10.5px;">
                        <td class="text-center">{{ idx + 1 }}</td>
                        <td class="pl-2 font-weight-bold">{{ row.name || '---' }}</td>
                        <td class="pl-2 text-uppercase">{{ row.latin_name || '---' }}</td>
                        <td class="text-center">{{ row.gender || '---' }}</td>
                        <td class="text-center">{{ row.dob ? formatDate(row.dob) : '---' }}</td>
                        <td class="pl-2">{{ row.occupation || '---' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- គ. ព័ត៌មានសហព័ទ្ធ (ប្តី ឬប្រពន្ធ) -->
              <div v-if="userProfile.spouse_name" class="mb-3 border-top pt-2">
                <div class="font-weight-bold text-success mb-2" style="font-size: 11.5px;">គ. ព័ត៌មានសហព័ទ្ធ (ប្តី ឬប្រពន្ធ)</div>
                <div class="pl-2">
                  <div class="row">
                    <div class="col-6">ឈ្មោះប្តី/ប្រពន្ធ៖ <span class="font-weight-bold text-success">{{ userProfile.spouse_name || '---' }}</span></div>
                    <div class="col-6">ជាអក្សរឡាតាំង៖ <span class="font-weight-bold text-uppercase">{{ userProfile.spouse_latin_name || '---' }}</span></div>
                  </div>
                  <div class="row mt-1">
                    <div class="col-4">ថ្ងៃកំណើត៖ <span>{{ userProfile.spouse_dob ? formatDate(userProfile.spouse_dob) : '---' }}</span></div>
                    <div class="col-4">សញ្ជាតិ៖ <span>{{ userProfile.spouse_nationality || '---' }}</span></div>
                    <div class="col-4">ស្ថានភាព៖ <span class="font-weight-bold">{{ userProfile.spouse_status || '---' }}</span></div>
                  </div>
                  <div class="row mt-1">
                    <div class="col-12">ទីកន្លែងកំណើត៖ <span>{{ userProfile.spouse_birthplace || '---' }}</span></div>
                  </div>
                  <div class="row mt-1">
                    <div class="col-6">មុខរបរ៖ <span>{{ userProfile.spouse_occupation || '---' }}</span></div>
                    <div class="col-6">ស្ថាប័ន/អង្គភាព៖ <span>{{ userProfile.spouse_unit || '---' }}</span></div>
                  </div>
                  <div class="row mt-1">
                    <div class="col-6">ប្រាក់ឧបត្ថម្ភ៖ <span class="font-weight-bold text-info">{{ userProfile.spouse_allowance || '---' }}</span></div>
                    <div class="col-6">លេខទូរស័ព្ទ៖ <span>{{ userProfile.spouse_phone || '---' }}</span></div>
                  </div>
                </div>
              </div>

              <!-- ឃ. ព័ត៌មានកូន -->
              <div v-if="userProfile.children?.length > 0" class="mb-3 border-top pt-2">
                <div class="font-weight-bold text-success mb-2" style="font-size: 11.5px;">ឃ. ព័ត៌មានកូន</div>
                <div class="table-responsive">
                  <table class="table table-bordered table-sm print-table mb-2">
                    <thead class="bg-light text-center">
                      <tr class="font-weight-bold" style="font-size: 10.5px;">
                        <th style="width: 40px">ល.រ</th>
                        <th>គោត្តនាម និងនាម</th>
                        <th>ជាអក្សរឡាតាំង</th>
                        <th style="width: 80px">ភេទ</th>
                        <th style="width: 120px">ថ្ងៃខែឆ្នាំកំណើត</th>
                        <th>មុខរបរ</th>
                        <th style="width: 180px">ប្រាក់ឧបត្ថម្ភ</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(row, idx) in userProfile.children" :key="'print-child-' + row.id" style="font-size: 10.5px;">
                        <td class="text-center">{{ idx + 1 }}</td>
                        <td class="pl-2 font-weight-bold">{{ row.name || '---' }}</td>
                        <td class="pl-2 text-uppercase">{{ row.latin_name || '---' }}</td>
                        <td class="text-center">{{ row.gender || '---' }}</td>
                        <td class="text-center">{{ row.dob ? formatDate(row.dob) : '---' }}</td>
                        <td class="pl-2">{{ row.occupation || '---' }}</td>
                        <td class="pl-2 font-weight-bold text-info">{{ row.allowance || '---' }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Signature Block (បំពេញដៃ) -->
              <div class="mt-4 pt-3 font-khmer print-signature-block" style="font-size: 11px; page-break-inside: avoid;">
                <div class="text-center font-weight-bold mb-3" style="font-size: 12px;">
                  ខ្ញុំសូមធានាថា អត្ថន័យទិន្នន័យទម្រង់ប្រវត្តិរូបសង្ខេបខាងលើនេះ ពិតជាត្រឹមត្រូវប្រាកដមែន។
                </div>
                
                <div class="row mt-2">
                  <!-- Left Column: Head of Unit -->
                  <div class="col-6 text-center">
                    <div>បានឃើញ និងបញ្ជាក់ ព័ត៌មានរបស់លោក/លោកស្រី <span class="font-weight-bold text-success">{{ userProfile.name_kh || userProfile.name || '---' }}</span></div>
                    <div class="font-weight-bold">ពិតជាត្រឹមត្រូវគ្រប់គ្រាន់ដូចប្រវត្តិរូបខាងលើនេះមែន។</div>
                    <div class="mt-2 text-muted">ថ្ងៃ.....................  ខែ..................  ឆ្នាំ....................  ព.ស.២៥.....</div>
                    <div class="mt-1 text-muted">ថ្ងៃទី........  ខែ........  ឆ្នាំ២០.....</div>
                    <div class="mt-5 font-weight-bold" style="font-size: 12px; margin-top: 50px !important;">ប្រធានអង្គភាព</div>
                  </div>
                  
                  <!-- Right Column: Subject -->
                  <div class="col-6 text-center">
                    <div class="text-muted">ថ្ងៃ.....................  ខែ..................  ឆ្នាំ....................  ព.ស.២៥.....</div>
                    <div class="mt-1 text-muted">ថ្ងៃទី........  ខែ........  ឆ្នាំ២០.....</div>
                    <div class="mt-5 font-weight-bold" style="font-size: 12px; margin-top: 65px !important;">ហត្ថលេខា និងឈ្មោះសាមីខ្លួន</div>
                  </div>
                </div>
              </div>

              <!-- Print Footer Block -->
              <div class="print-footer mt-5 pt-3 border-top d-flex justify-content-between align-items-end font-khmer" style="font-size: 10px; line-height: 1.4;">
                <div class="text-left text-muted">
                  <div>{{ getFormattedPrintDateTime() }}</div>
                  <div>រក្សាសិទ្ធិក្នុងការប្រើប្រាស់ទិន្នន័យ</div>
                </div>
                <div class="text-center text-muted font-weight-bold print-page-counter" style="font-size: 11px;">
                  <!-- Injected via CSS print counter -->
                </div>
                <div class="text-right">
                  <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' + getQrCodeData()" alt="QR Code" class="print-qr-code" style="width: 45px; height: 45px; object-fit: contain;" />
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
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useUserStore } from "@/stores/user";
import { apiReadUser, apiGetMyProfile } from "@/functions/api/user";
import emptyImage from "@/assets/images/emptyImage.png";
import ministryLogo from "@/assets/images/logoImage.webp";

const route = useRoute();
const userStore = useUserStore();
const userProfile = ref({});

onMounted(async () => {
  try {
    const userId = route.params.id; 
    let response;

    if (userId) {
      response = await apiReadUser(userId);
    } else {
      response = await apiGetMyProfile();
    }

    const userData = response.data.user || response.data.data || response.data;
    
    if (userData) {
      userProfile.value = userData;
      
      // Update global user store ONLY if the logged-in user is viewing their own profile
      if (!userId && typeof userStore.setState === 'function') {
        userStore.setState(userData);
      }
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
  const g = String(gender).trim().toLowerCase();
  if (g === 'male' || g === 'm') return 'ប្រុស';
  if (g === 'female' || g === 'f') return 'ស្រី';
  return gender;
};

const formatMaritalStatus = (status) => {
  if (!status) return '---';
  const s = String(status).trim().toLowerCase();
  if (s === 'single') return 'នៅលីវ';
  if (s === 'married') return 'រៀបការរួច';
  if (s === 'divorced') return 'លែងលះ';
  if (s === 'widowed') return 'មេម៉ាយ/ពោះម៉ាយ';
  return status;
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
    if (years > 0) parts.push(`${toKhmerNum(years)} ឆ្នាំ`);
    if (months > 0) parts.push(`${toKhmerNum(months)} ខែ`);
    if (years === 0 && months === 0) {
      parts.push(days > 0 ? `${toKhmerNum(days)} ថ្ងៃ` : 'ទើបចូលបម្រើការងារ');
    }
    return parts.join(' ');
  } catch (e) {
    return null;
  }
};

const printProfile = () => {
  window.print();
};

const educationsByCategory = (category) => {
  return (userProfile.value.educations || []).filter(e => e.category === category);
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
  return encodeURIComponent(window.location.origin + '/users/profile/' + (userProfile.value?.id || ''));
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;700&display=swap');

.text-success,
.text-info,
.text-danger {
  color: #000 !important;
}

@font-face {
  font-family: 'Khmer OS Moul Light';
  src: local('Khmer OS Moul Light'), local('Moul');
}

@font-face {
  font-family: 'Khmer OS Siemreap';
  src: local('Khmer OS Siemreap'), local('Siemreap');
}

.print-page,
.content-header {
  font-family: 'Khmer OS Siemreap', sans-serif !important;
  color: #000;
}

.page-title {
  font-family: 'Khmer OS Siemreap', sans-serif !important;
  font-size: 18px;
}

.kingdom-title,
.tr-title,
.ga-title,
.main-doc-title,
.section-title {
  font-family: 'Khmer OS Moul Light', serif !important;
}

.a4-sheet {
  font-family: 'Khmer OS Siemreap', sans-serif !important;
  color: #000;
  width: 210mm;
  min-height: 297mm;
  padding: 15mm 20mm;
  margin: 0 auto;
  background: white;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  box-sizing: border-box;
}

.tr-title {
  font-size: 12px;
  line-height: 1.3;
}

.ga-title {
  font-size: 12px;
  line-height: 1.3;
}

.kingdom-title {
  font-size: 16px;
  line-height: 1.5;
}

.main-doc-title {
  font-size: 14px;
  margin-bottom: 2px;
}

.ministry-logo {
  width: 80px;
  height: 80px;
  object-fit: contain;
}

.profile-photo {
  width: 95px;
  height: 115px;
  object-fit: cover;
  border-radius: 2px;
}

.section-title {
  font-size: 12px;
  padding-bottom: 2px;
  margin-top: 15px;
}

.print-table {
  border: 1px solid #333 !important;
  border-collapse: collapse !important;
  width: 100%;
}
.print-table th, .print-table td {
  border: 1px solid #333 !important;
  padding: 4px 6px !important;
  vertical-align: middle;
  word-break: break-word !important;
}

.label-title {
  font-size: 12px;
  font-weight: bold;
}

.label-value {
  font-size: 12px;
}

@media print {
  .print-hide {
    display: none !important;
  }

  .content-wrapper,
  body,
  html {
    background-color: white !important;
    padding: 0 !important;
    margin: 0 !important;
  }

  .a4-sheet {
    width: 100% !important;
    min-height: 0 !important;
    box-shadow: none !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  @page {
    size: A4 portrait;
    margin: 20mm 15mm 30mm 15mm; /* Margins for top (20mm), right (15mm), bottom (30mm), left (15mm) on every page */
  }

  /* Prevent table rows and headers from splitting or clipping horizontally */
  tr, thead, tbody {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  /* Prevent section titles and signature blocks from breaking awkwardly */
  .section-title,
  .print-signature-block,
  h5,
  h6 {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  /* Fixed Footer on Every Printed Page */
  .print-footer {
    position: fixed !important;
    bottom: 12mm !important; /* Raised up to sit safely inside the printable margin area */
    left: 0 !important;
    right: 0 !important;
    height: 15mm !important;
    background: white !important;
    border-top: 1px solid #333 !important;
    padding-top: 5px !important;
    display: flex !important;
    justify-content: space-between !important;
    align-items: flex-end !important;
    z-index: 9999;
  }

  @counter-style khmer {
    system: numeric;
    symbols: '០' '១' '២' '៣' '៤' '៥' '៦' '៧' '៨' '៩';
  }

  .print-page-counter::after {
    content: counter(page, khmer) " / " counter(pages, khmer);
    font-size: 11px;
  }
}
</style>

<style>
@media print {
  /* Hides sidebar, header/navbar, and main footer from printing globally */
  .main-header,
  .main-sidebar,
  .main-footer,
  .control-sidebar,
  nav,
  aside {
    display: none !important;
  }

  /* Resets wrapper offset and spacing globally during print */
  html,
  body,
  #app,
  .wrapper,
  .content-wrapper,
  .content,
  .container-fluid {
    margin-left: 0 !important;
    padding: 0 !important;
    margin-top: 0 !important;
    border: none !important;
    background-color: white !important;
    overflow: visible !important;
    height: auto !important;
    min-height: 0 !important;
    position: static !important;
  }
}
</style>
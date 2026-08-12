<template>
  <div class="content-wrapper-custom">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 class="m-0 font-weight-bold text-dark-custom">ផ្ទាំងបញ្ជា (Dashboard)</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><router-link to="/">ទំព័រដើម</router-link></li>
              <li class="breadcrumb-item active">ផ្ទាំងបញ្ជា</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- 1. Small Stat Boxes Grid -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box custom-stat-card">
              <div class="inner">
                <h3>1,250</h3>
                <p>មន្ត្រីសរុប</p>
              </div>
              <div class="icon">
                <i class="fas fa-users"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box custom-stat-card">
              <div class="inner">
                <h3>1,180</h3>
                <p>កំពុងបំពេញការងារ</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-check"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box custom-stat-card">
              <div class="inner">
                <h3>45</h3>
                <p>ច្បាប់ / សម្រាក</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-clock"></i>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-6">
            <div class="small-box custom-stat-card">
              <div class="inner">
                <h3>25</h3>
                <p>ចូលនិវត្តន៍</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-minus"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- 2. Data Table / Main Card -->
        <div class="row mt-3">
          <div class="col-12">
            <div class="card card-outline-custom">
              <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">បញ្ជីសកម្មភាព / មន្ត្រីទើបចុះឈ្មោះ</h3>
              </div>
              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-valign-middle">
                  <thead>
                    <tr>
                      <th>អត្តលេខ</th>
                      <th>ឈ្មោះមន្ត្រី</th>
                      <th>តួនាទី</th>
                      <th>អង្គភាព</th>
                      <th>ស្ថានភាព</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>#OFF-001</td>
                      <td>សុក ចាន់ដារ៉ា</td>
                      <td>ប្រធានការិយាល័យ</td>
                      <td>ព័ត៌មានវិទ្យា</td>
                      <td><span class="badge badge-success-custom">សកម្ម</span></td>
                    </tr>
                    <tr>
                      <td>#OFF-002</td>
                      <td>កែវ សុភា</td>
                      <td>អនុប្រធាននាយកដ្ឋាន</td>
                      <td>បុគ្គលិក</td>
                      <td><span class="badge badge-success-custom">សកម្ម</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@400;500;600;700&display=swap');

.content-wrapper-custom {
  font-family: 'Kantumruy Pro', sans-serif;
}

.text-dark-custom {
  color: #0c2b29 !important;
}

/* Stat Cards Styling */
.custom-stat-card {
  background-color: #ffffff !important;
  border-radius: 12px !important;
  border: 1px solid #e2e8f0 !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03) !important;
}

.custom-stat-card .inner h3 {
  color: #0c2b29 !important;
  font-weight: 700;
}

.custom-stat-card .inner p {
  color: #64748b !important;
  font-size: 13px;
}

.custom-stat-card .icon i {
  color: #0c2b29;
  opacity: 0.15;
}

/* Table Card Styling */
.card-outline-custom {
  border-radius: 12px !important;
  border: 1px solid #e2e8f0 !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03) !important;
}

.badge-success-custom {
  background-color: #e6f2f1 !important;
  color: #0c2b29 !important;
  padding: 6px 12px;
  border-radius: 6px;
}
</style>
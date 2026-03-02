<template>
  <div v-if="isAuthenticate">
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h3>Summary Report</h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                  <router-link :to="{name: 'Home'}">Home</router-link>
                </li>
                <li class="breadcrumb-item active">Summary Report</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <div class="text-center" v-if="loader">
          <div class="spinner-border" role="status">
            <span class="sr-only"><p style="color:red">Loading...</p></span>
          </div>
        </div>
        <div class="card" v-else>
          <div class="col-md-12 mb-5"></div>
          <div class="col-md-12 mb-3">
            <form class="form-inline" @submit.prevent="fetchReport()">
              <div class="form-group">
                <p class="form-control-static">From Date</p>
              </div>
              <div class="form-group mx-sm-3">
                <input type="date" class="form-control" v-model="formData.fromDate">
              </div>
              <div class="form-group">
                <p class="form-control-static">To Date</p>
              </div>
              <div class="form-group mx-sm-3">
                <input type="date" class="form-control" v-model="formData.toDate">
              </div>
              <div class="form-group">
                <p class="form-control-static">Business</p>
              </div>
              <div class="form-group mx-sm-3">
                <select class="form-control" v-model="formData.business">
                  <option value="">All</option>
                  <option value="K">K-Pump</option>
                  <option value="L">L-Tools</option>
                </select>
              </div>
              <div class="form-group">
                <button type="submit" class="btn btn-default button-border">
                  <i class="fas fa-filter"></i> Filter
                </button>
              </div>
              <div class="form-group mx-sm-3">
                <button type="button" @click.prevent="clearFilter()" class="btn btn-default button-border">
                  Clear Filter
                </button>
              </div>
            </form>
          </div>
          <div class="col-md-12">
            <div class="input-group">
              <div class="col-md-8" style="padding-left: 0px;">
                <button class="btn btn-default button-border" @click.prevent="print">
                  <i class="fas fa-print"></i> Print
                </button>
                <a @click.prevent="">
                  <export-excel
                      class="btn btn-default button-border"
                      :data="json_data"
                      :fields="json_fields"
                      worksheet="Summary Report"
                      name="summary-report.xls">
                    Excel
                  </export-excel>
                </a>
              </div>
            </div>
            <div ref="document" class="table-responsive" style="margin-top: 20px;">
              <table id="printMe" class="table table-bordered table-striped dt-responsive nowrap dataTable no-footer dtr-inline table-sm small">
                <thead>
                  <tr>
                    <th>SL</th>
                    <th>Staff ID</th>
                    <th>Staff Name</th>
                    <th>Total Services</th>
                    <th>Total Service Charge</th>
                    <th>Total Service Cost</th>
                    <th>Avg Service Charge</th>
                    <th>Business</th>
                    <th>Service Type</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-if="data.length > 0">
                    <tr v-for="(item, index) in data" :key="index">
                      <td>{{ index + 1 }}</td>
                      <td>{{ item.StaffID }}</td>
                      <td>{{ item.StaffName }}</td>
                      <td>{{ item.TotalServices }}</td>
                      <td>{{ item.TotalServiceCharge }}</td>
                      <td>{{ item.TotalServiceCost }}</td>
                      <td>{{ item.AvgServiceCharge }}</td>
                      <td>{{ item.Business }}</td>
                      <td>{{ item.ServiceType }}</td>
                    </tr>
                  </template>
                  <template v-else>
                    <tr>
                      <td style="text-align: center;" colspan="9">No Data</td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <div class="col-md-12 mb-3 mt-3" v-else>Please Login</div>
</template>

<script>
import moment from 'moment'

export default {
  data: () => {
    return {
      base_url: '/waterpump/public',
      data: [],
      formData: {
        fromDate: moment().startOf('month').format('YYYY-MM-DD'),
        toDate: moment().endOf('month').format('YYYY-MM-DD'),
        business: ''
      },
      isAuthenticate: '',
      loader: false,
      json_fields: {
        'SL': 'SL',
        'Staff ID': 'StaffID',
        'Staff Name': 'StaffName',
        'Total Services': 'TotalServices',
        'Total Service Charge': 'TotalServiceCharge',
        'Total Service Cost': 'TotalServiceCost',
        'Avg Service Charge': 'AvgServiceCharge',
        'Business': 'Business',
        'Service Type': 'ServiceType'
      },
      json_data: []
    };
  },
  created() {
    if (localStorage.getItem('auth') != null) {
      this.isAuthenticate = true;
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('auth');
    } else {
      this.isAuthenticate = false;
      this.$router.push({ name: 'Login' });
    }
  },
  mounted() {
    this.loader = true;
    this.fetchReport();
  },
  methods: {
    fetchReport() {
      this.loader = true;
      axios.get(this.base_url + '/api/admindashboard/servicesummaryreport', { params: this.formData })
          .then((response) => {
            this.loader = false;
            this.data = response.data.data;
            this.json_data = response.data.data.map((item, index) => {
              return { SL: index + 1, ...item };
            });
          })
          .catch(() => {
            this.loader = false;
            this.$toastr.error('Something went wrong.');
          });
    },
    clearFilter() {
      this.formData.fromDate = moment().startOf('month').format('YYYY-MM-DD');
      this.formData.toDate = moment().endOf('month').format('YYYY-MM-DD');
      this.formData.business = '';
      this.fetchReport();
    },
    print() {
      this.$htmlToPaper('printMe', null, () => {});
    },
    moment: function (date) {
      return moment(date);
    }
  },
};
</script>

<style scoped>
.button-border {
  border-color: #0042ff
}
</style>

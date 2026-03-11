<template>
  <div v-if="isAuthenticate">
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h3>Provided Service Summary Report</h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                  <router-link :to="{name: 'Home'}">Home</router-link>
                </li>
                <li class="breadcrumb-item active">Provided Service Summary Report</li>
              </ol>
            </div>
          </div>
        </div>
      </section>
      <section class="content">
        <!-- Loader -->
        <div class="modern-loader" v-if="loader">
          <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
          </div>
          <p class="mt-2 text-muted">Loading report data...</p>
        </div>

        <div class="modern-card" v-else>
          <!-- Filter Section -->
          <div class="filter-section">
            <div class="filter-header">
              <i class="fas fa-sliders-h"></i> Filters
            </div>
            <div class="filter-body">
              <div class="filter-group">
                <label class="filter-label">From Date</label>
                <input class="form-control filter-input" type="date" v-model="tableData.fromDate">
              </div>
              <div class="filter-group">
                <label class="filter-label">To Date</label>
                <input class="form-control filter-input" type="date" v-model="tableData.toDate">
              </div>
              <div class="filter-group">
                <label class="filter-label">Service Time</label>
                <select class="form-control filter-input" v-model="tableData.serviceTime">
                  <option value="">All</option>
                  <option value="First">First</option>
                  <option value="Second">Second</option>
                  <option value="Third">Third</option>
                  <option value="Fourth">Fourth</option>
                </select>
              </div>
              <div class="filter-group">
                <label class="filter-label">Business</label>
                <select class="form-control filter-input" v-model="tableData.business">
                  <option value="">All</option>
                  <option value="K">K-Pump</option>
                  <option value="L">L-Tools</option>
                </select>
              </div>
              <div class="filter-actions">
                <button @click.prevent="allService()" class="btn btn-filter-apply">
                  <i class="fas fa-filter"></i> Apply
                </button>
                <button @click.prevent="clearFilter()" class="btn btn-filter-clear">
                  <i class="fas fa-times"></i> Clear
                </button>
              </div>
            </div>
          </div>

          <!-- Toolbar Section -->
          <div class="toolbar-section">
            <div class="toolbar-left">
              <button class="btn btn-tool" @click.prevent="print">
                <i class="fas fa-print"></i> Print
              </button>
              <a @click.prevent="exportReport">
                <export-excel
                    class="btn btn-tool btn-tool-excel"
                    :data="json_data"
                    :fields="json_fields"
                    worksheet="My Worksheet"
                    name="service-summary-report.xls">
                  <i class="fas fa-file-excel"></i> Excel
                </export-excel>
              </a>
            </div>
            <div class="toolbar-right">
              <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input class="form-control search-input" type="text" placeholder="Search..." v-model="tableData.search" @input="allService()">
              </div>
            </div>
          </div>

          <!-- Table Section -->
          <div class="table-section" ref="document">
            <data-table :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" @sort="sortBy" id="printMe">
              <tbody>
              <tr v-for="(providedService, index) in providedServiceList" :key="providedService.ServiceMasterID" :class="{'row-even': index % 2 === 0, 'row-odd': index % 2 !== 0}">
                <td class="td-id">{{ providedService.ServiceMasterID }}</td>
                <td>{{ providedService.StaffID }}</td>
                <td>{{ providedService.StaffName }}</td>
                <td>{{ providedService.TTYName }}</td>
                <td class="td-name">{{ providedService.CustomerName }}</td>
                <td>{{ providedService.DistrictName }}</td>
                <td>{{ providedService.Address }}</td>
                <td>{{ providedService.Mobile }}</td>
                <td>{{ providedService.AttendDate }}</td>
                <td>{{ providedService.BrandCode }}</td>
                <td>{{ providedService.Brandname }}</td>
                <td>{{ providedService.PurchaseDate }}</td>
                <td>{{ providedService.ActionTaken }}</td>
                <td>
                  <span class="badge-service-time" :class="'badge-st-' + (providedService.ServiceTime || '').toLowerCase()">
                    {{ providedService.ServiceTime }}
                  </span>
                </td>
                <td>
                  <span class="badge-business" :class="providedService.Business === 'K' ? 'badge-biz-k' : 'badge-biz-l'">
                    {{ providedService.Business === 'K' ? 'K-Pump' : providedService.Business === 'L' ? 'L-Tools' : providedService.Business }}
                  </span>
                </td>
                <td class="td-charge">{{ providedService.ServiceCharge }}</td>
                <td>{{ providedService.MRNo }}</td>
                <td>{{ providedService.WarrantyCardNo }}</td>
                <td class="td-point">
                  <span class="point-badge" :class="providedService.Point > 0 ? 'point-has' : 'point-zero'">
                    {{ providedService.Point || 0 }}
                  </span>
                </td>
                <td class="td-date">{{ providedService.EntryDate }}</td>
                <td class="td-remarks">
                  <popper
                      v-if="providedService.Remarks"
                      trigger="clickToOpen" :options="{
                                  placement: 'bottom',
                                  modifiers: { offset: { offset: '0,10px' } }
                                  }">
                    <div class="popper remarks-popper">
                      <div class="remarks-content">
                        {{ providedService.Remarks }}
                      </div>
                    </div>
                    <a slot="reference" class="btn-remarks btn-remarks-has">
                      <i class="fas fa-comment-dots"></i> View
                    </a>
                  </popper>
                  <span class="btn-remarks btn-remarks-none" v-else>
                    <i class="fas fa-comment-slash"></i> None
                  </span>
                </td>
              </tr>
              <tr v-if="providedServiceList.length === 0">
                <td colspan="21" class="td-empty">
                  <i class="fas fa-inbox"></i>
                  <p>No records found</p>
                </td>
              </tr>
              </tbody>
            </data-table>
          </div>

          <!-- Footer Section -->
          <div class="table-footer">
            <div class="footer-info">
              <span class="text-muted" v-if="pagination.from">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries
              </span>
            </div>
            <div class="footer-pagination">
              <nav>
                <ul class="modern-pagination">
                  <li :class="['page-item', { disabled: !pagination.prevPageUrl }]">
                    <a class="page-link" @click="pagination.prevPageUrl && allService(pagination.prevPageUrl)">
                      <i class="fas fa-chevron-left"></i>
                    </a>
                  </li>
                  <li v-for="page in visiblePages" :key="page" :class="['page-item', { active: page === pagination.currentPage, ellipsis: page === '...' }]">
                    <span v-if="page === '...'" class="page-link page-ellipsis">...</span>
                    <a v-else class="page-link" @click="goToPage(page)">{{ page }}</a>
                  </li>
                  <li :class="['page-item', { disabled: !pagination.nextPageUrl }]">
                    <a class="page-link" @click="pagination.nextPageUrl && allService(pagination.nextPageUrl)">
                      <i class="fas fa-chevron-right"></i>
                    </a>
                  </li>
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
  <div class="col-md-12 mb-3 mt-3" v-else>Please Login</div>
</template>

<script>
import DataTable from '../datatable/DataTable';
import moment from 'moment'
import html2pdf from 'html2pdf.js'
import Popper from 'vue-popperjs';
import 'vue-popperjs/dist/vue-popper.css';

export default {
  components: {
    'data-table': DataTable,
    'popper'    : Popper
  },
  data: () => {
    let sortOrders = {};
    let columns = [
      {width: '20%', label: 'SL', name: 'ServiceMasterID'},
      {width: '20%', label: 'StaffID', name: 'StaffID'},
      {width: '20%', label: 'StaffName', name: 'UserName'},
      {width: '20%', label: 'Territory', name: 'Territory'},
      {width: '20%', label: 'CustomerName', name: 'CustomerName'},
      {width: '20%', label: 'District', name: 'District'},
      {width: '20%', label: 'Address', name: 'Address'},
      {width: '20%', label: 'CustomerMobile', name: 'CustomerMobile'},
      {width: '20%', label: 'AttendDate', name: 'AttendDate'},
      {width: '20%', label: 'ProductCode', name: 'ProductCode'},
      {width: '20%', label: 'ModelName', name: 'Model'},
      {width: '20%', label: 'PurchaseDate', name: 'PurchaseDate'},
      {width: '20%', label: 'ActionTaken', name: 'Action'},
      {width: '20%', label: 'ServiceTime', name: 'ServiceTime'},
      {width: '20%', label: 'Business', name: 'Business'},
      {width: '20%', label: 'ServiceCharge', name: 'Service Charge'},
      {width: '20%', label: 'MRNo.', name: 'MRNo'},
      {width: '20%', label: 'WarrantyCard No.', name: 'WarrantyCardNo'},
      {width: '20%', label: 'RatingPoint', name: 'RattingPoint'},
      {width: '20%', label: 'EntryDate', name: 'EntryDate'},
      {width: '20%', label: 'Remarks', name: 'Remarks'},
    ];
    columns.forEach((column) => {
      sortOrders[column.name] = 1;
    });
    return {
      base_url: '/waterpump/public',
      providedServiceList: [],
      //   serviceDetails: [],
      columns: columns,
      sortKey: 'StaffID',
      sortOrders: sortOrders,
      perPage: ['50', '100', '200'],
      tableData: {
        draw: 0,
        length: 50,
        search: '',
        column: 0,
        dir: 'desc',
        fromDate: '',
        toDate: '',
        serviceTime: '',
        business: ''
      },
      pagination: {
        lastPage: '',
        currentPage: '',
        total: '',
        lastPageUrl: '',
        nextPageUrl: '',
        prevPageUrl: '',
        from: '',
        to: ''
      },
      isAuthenticate: '',
      loader: false,
      opened: [],
      expand: false,
      masterID: {
        serviceMasterId: ''
      },
      json_fields: {
        'SL': 'ServiceMasterID',
        'Staff ID': 'StaffID',
        'Staff Name': 'StaffName',
        'Territory': 'TTYName',
        'Customer Name': 'CustomerName',
        'District': 'DistrictName',
        'Address': 'Address',
        'Customer Mobile': 'Mobile',
        'Attend Date': 'AttendDate',
        'Product Code': 'ModelCode',
        'Model': 'Brandname',
        'Purchase Date': 'PurchaseDate',
        'Action': 'ActionTaken',
        'ServiceTime': 'ServiceTime',
        'Business': 'Business',
        'Service Charge': 'ServiceCharge',
        'MRNo.': 'MRNo',
        'Warranty Card Number': 'WarrantyCardNo',
        'Rating point': 'Point',
        'EntryDate': 'EntryDate',
      },
      json_data: [],
      json_meta: [
        [
          {
            'key': 'charset',
            'value': 'utf-8'
          }
        ]
      ],
    };
  },
  computed: {
    visiblePages() {
      const current = this.pagination.currentPage;
      const last = this.pagination.lastPage;
      if (!last || last <= 1) return [];
      const pages = [];
      if (last <= 7) {
        for (let i = 1; i <= last; i++) pages.push(i);
      } else {
        pages.push(1);
        if (current > 3) pages.push('...');
        let start = Math.max(2, current - 1);
        let end = Math.min(last - 1, current + 1);
        if (current <= 3) { start = 2; end = 4; }
        if (current >= last - 2) { start = last - 3; end = last - 1; }
        for (let i = start; i <= end; i++) pages.push(i);
        if (current < last - 2) pages.push('...');
        pages.push(last);
      }
      return pages;
    }
  },
  created() {
    if (localStorage.getItem('auth') != null) {
      this.isAuthenticate = true;
      this.userId = this.$store.state.userid;
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.$store.state.token;
      this.allService();
    } else {
      this.isAuthenticate = false;
      this.$router.push(this.base_url + "/login");
    }
  },
  mounted() {
    this.loader = true;
  },
  methods: {
    allService(url = this.base_url + "/api/admindashboard/providedservicereport") {
      this.tableData.draw++;
      axios
          .get(url, {params: this.tableData}, {token: this.$store.state.token})
          .then((response) => {
            this.loader = false;
            if (this.tableData.draw == response.data.serviceMaster.draw) {
              this.providedServiceList = response.data.serviceMaster.data.data;
              this.json_data = response.data.serviceMaster.data.data;
              this.configPagination(response.data.serviceMaster.data);
            }
            this.exportReport();
          })
          .catch((error) => {
            this.$toastr.error('Something went wrong.');
          });
    },
    configPagination(data) {
      this.pagination.lastPage = data.last_page;
      this.pagination.currentPage = data.current_page;
      this.pagination.total = data.total;
      this.pagination.lastPageUrl = data.last_page_url;
      this.pagination.nextPageUrl = data.next_page_url;
      this.pagination.prevPageUrl = data.prev_page_url;
      this.pagination.from = data.from;
      this.pagination.to = data.to;
    },
    goToPage(page) {
      if (page === this.pagination.currentPage) return;
      const url = this.base_url + '/api/admindashboard/providedservicereport?page=' + page;
      this.allService(url);
    },
    sortBy(key) {
      this.sortKey = key;
      this.sortOrders[key] = this.sortOrders[key] * -1;
      this.tableData.column = this.getIndex(this.columns, 'name', key);
      this.tableData.dir = this.sortOrders[key] === 1 ? 'asc' : 'desc';
      this.allService();
    },
    getIndex(array, key, value) {
      return array.findIndex(i => i[key] == value)
    },
    toggle(id) {
      const index = this.opened.indexOf(id);
      if (index > -1) {
        this.opened.splice(index, 1)
        // this.expand = false
      } else {
        this.opened.splice(index, 1)

        this.opened.push(id)
        if (id != 0) {
          this.masterID.serviceMasterId = id;
          axios
              .get(this.base_url + '/api/admindashboard/providedserviceDetails', {params: this.masterID}, {token: this.$store.state.token})
              .then((response) => {
                if (response.data.status == true) {
                  this.serviceDetails = response.data.serviceDetails;
                  // this.expand = true
                }
              })
              .catch((error) => {
                this.$toastr.error('Something went wrong.');
              });
        }
      }
    },
    toggleIcon(img) {
      return window.location.origin + img
    },
    clearFilter() {
      this.tableData.fromDate = '';
      this.tableData.toDate = '';
      this.tableData.serviceTime = '';
      this.tableData.business = '';
      this.allService();
      this.exportReport();
    },
    print() {
      // Pass the element id here
      this.$htmlToPaper('printMe', null, () => {
      });
    },
    exportReport() {
      axios.get(this.base_url + '/api/admindashboard/exportServiceReport', {params: this.tableData}, {token: this.$store.state.token}).then((response) => {
        this.json_data = [];
        this.json_data = response.data;
      }).catch((error) => {
        this.$toastr.error('Something went wrong.');
      });
    },
    moment: function (date) {
      return moment(date);
    },
    exportToPDF() {
      html2pdf(this.$refs.document, {
        margin: 1,
        filename: 'service-summary-report.pdf',
        image: {type: 'jpeg', quality: 1},
        html2canvas: {scale: 2},
        pagebreak: {mode: ['avoid-all', 'css', 'legacy']},
        jsPDF: {
          unit: 'mm', format: 'a4', orientation: 'landscape',
          putOnlyUsedFonts: true, floatPrecision: 12, compress: true, fontSize: 8
        }
      })
    }
  },
};
</script>

<style scoped>
/* ====== Loader ====== */
.modern-loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 80px 0;
}

/* ====== Card Container ====== */
.modern-card {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  padding: 0;
  overflow: hidden;
}

/* ====== Filter Section ====== */
.filter-section {
  border-bottom: 1px solid #e9ecef;
}
.filter-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  padding: 10px 20px;
  font-weight: 600;
  font-size: 13px;
  color: #495057;
  letter-spacing: 0.5px;
}
.filter-header i {
  margin-right: 8px;
  color: #6c757d;
}
.filter-body {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 15px;
  padding: 15px 20px;
}
.filter-group {
  display: flex;
  flex-direction: column;
  min-width: 150px;
}
.filter-label {
  font-size: 11px;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}
.filter-input {
  border-radius: 6px;
  border: 1.5px solid #dee2e6;
  font-size: 13px;
  padding: 6px 10px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.filter-input:focus {
  border-color: #4a90d9;
  box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.15);
}
.filter-actions {
  display: flex;
  gap: 8px;
  align-items: flex-end;
  padding-bottom: 1px;
}
.btn-filter-apply {
  background: linear-gradient(135deg, #4a90d9 0%, #357abd 100%);
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 7px 18px;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s;
}
.btn-filter-apply:hover {
  background: linear-gradient(135deg, #357abd 0%, #2868a8 100%);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(53, 122, 189, 0.3);
  color: #fff;
}
.btn-filter-clear {
  background: #fff;
  color: #6c757d;
  border: 1.5px solid #dee2e6;
  border-radius: 6px;
  padding: 6px 16px;
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s;
}
.btn-filter-clear:hover {
  background: #f8f9fa;
  border-color: #adb5bd;
  color: #495057;
}

/* ====== Toolbar Section ====== */
.toolbar-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  padding: 15px 20px;
  border-bottom: 1px solid #e9ecef;
}
.toolbar-left {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}
.btn-tool {
  background: #fff;
  border: 1.5px solid #dee2e6;
  border-radius: 6px;
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 500;
  color: #495057;
  transition: all 0.2s;
}
.btn-tool:hover {
  background: #f8f9fa;
  border-color: #4a90d9;
  color: #4a90d9;
  transform: translateY(-1px);
}
.btn-tool i {
  margin-right: 5px;
}
.btn-tool-excel {
  background: #fff;
  border: 1.5px solid #dee2e6;
  border-radius: 6px;
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 500;
  color: #495057;
  transition: all 0.2s;
}
.btn-tool-excel:hover {
  background: #f8f9fa;
  border-color: #1d6f42;
  color: #1d6f42;
}
.toolbar-right {
  min-width: 250px;
}
.search-box {
  position: relative;
}
.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #adb5bd;
  font-size: 13px;
  z-index: 2;
}
.search-input {
  padding-left: 36px;
  border-radius: 20px;
  border: 1.5px solid #dee2e6;
  font-size: 13px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input:focus {
  border-color: #4a90d9;
  box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.15);
}

/* ====== Table Section ====== */
.table-section {
  padding: 0 20px;
}
.table-section >>> .tableFixHead {
  border-radius: 8px;
  border: 1px solid #e9ecef;
  overflow: hidden;
  overflow-y: auto;
}
.table-section >>> table {
  margin-bottom: 0;
}
.table-section >>> thead th {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
  color: #fff !important;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 10px 8px;
  border: none;
  white-space: nowrap;
  position: sticky;
  top: 0;
  z-index: 1;
}
.table-section >>> tbody td {
  padding: 8px 8px;
  font-size: 12px;
  color: #495057;
  vertical-align: middle;
  border-color: #f0f0f0;
  transition: background-color 0.15s;
}
.table-section >>> tbody tr:hover td {
  background-color: #e8f4fd !important;
}
.row-even td {
  background-color: #fff;
}
.row-odd td {
  background-color: #f8f9fb;
}
.td-id {
  font-weight: 600;
  color: #2c3e50 !important;
}
.td-name {
  font-weight: 500;
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.td-charge {
  font-weight: 600;
  color: #2c3e50 !important;
}
.td-date {
  white-space: nowrap;
  font-size: 11px;
}
.td-remarks {
  text-align: center;
}
.td-empty {
  text-align: center;
  padding: 40px 20px !important;
  color: #adb5bd;
}
.td-empty i {
  font-size: 32px;
  display: block;
  margin-bottom: 8px;
}
.td-empty p {
  margin: 0;
  font-size: 14px;
}

/* ====== Badges ====== */
.badge-service-time {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
.badge-st-first {
  background: #dfe6e9;
  color: #2d3436;
}
.badge-st-second {
  background: #ffeaa7;
  color: #6c5c00;
}
.badge-st-third {
  background: #fab1a0;
  color: #6b2b1a;
}
.badge-st-fourth {
  background: #a29bfe;
  color: #fff;
}
.badge-business {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.3px;
}
.badge-biz-k {
  background: linear-gradient(135deg, #0984e3, #74b9ff);
  color: #fff;
}
.badge-biz-l {
  background: linear-gradient(135deg, #00b894, #55efc4);
  color: #fff;
}

/* ====== Points ====== */
.td-point {
  text-align: center;
}
.point-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  font-size: 11px;
  font-weight: 700;
}
.point-has {
  background: linear-gradient(135deg, #00b894, #55efc4);
  color: #fff;
}
.point-zero {
  background: #f1f2f6;
  color: #b2bec3;
}

/* ====== Remarks ====== */
.btn-remarks {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 10px;
  border-radius: 5px;
  font-size: 11px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-remarks i {
  font-size: 10px;
}
.btn-remarks-has {
  background: linear-gradient(135deg, #0984e3, #74b9ff);
  color: #fff !important;
  text-decoration: none !important;
}
.btn-remarks-has:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(9, 132, 227, 0.3);
}
.btn-remarks-none {
  background: #f1f2f6;
  color: #b2bec3;
}
.remarks-popper {
  background-color: #2c3e50 !important;
  color: #fff !important;
  font-size: 12px;
  border-radius: 8px;
  padding: 10px 14px;
  max-width: 300px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}
.remarks-content {
  line-height: 1.5;
}

/* ====== Footer ====== */
.table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 20px;
  border-top: 1px solid #e9ecef;
  background: #f8f9fa;
  border-radius: 0 0 12px 12px;
}
.footer-info {
  font-size: 13px;
}

/* ====== Modern Pagination ====== */
.modern-pagination {
  display: flex;
  list-style: none;
  padding: 0;
  margin: 0;
  gap: 4px;
  align-items: center;
}
.modern-pagination .page-item .page-link {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 34px;
  height: 34px;
  padding: 0 8px;
  border-radius: 8px;
  border: 1.5px solid #dee2e6;
  background: #fff;
  color: #495057;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
  user-select: none;
}
.modern-pagination .page-item .page-link:hover {
  background: #e8f4fd;
  border-color: #4a90d9;
  color: #4a90d9;
}
.modern-pagination .page-item.active .page-link {
  background: linear-gradient(135deg, #4a90d9 0%, #357abd 100%);
  color: #fff;
  border-color: #357abd;
  box-shadow: 0 2px 8px rgba(74, 144, 217, 0.3);
  font-weight: 600;
}
.modern-pagination .page-item.disabled .page-link {
  background: #f8f9fa;
  color: #ced4da;
  border-color: #e9ecef;
  cursor: not-allowed;
  pointer-events: none;
}
.modern-pagination .page-item.ellipsis .page-ellipsis {
  border: none;
  background: transparent;
  color: #6c757d;
  cursor: default;
  min-width: 24px;
  font-weight: 700;
  letter-spacing: 1px;
}
.modern-pagination .page-item .page-link i {
  font-size: 11px;
}
</style>

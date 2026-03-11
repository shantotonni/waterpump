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
                <input type="date" class="form-control filter-input" v-model="formData.fromDate">
              </div>
              <div class="filter-group">
                <label class="filter-label">To Date</label>
                <input type="date" class="form-control filter-input" v-model="formData.toDate">
              </div>
              <div class="filter-group">
                <label class="filter-label">Business</label>
                <select class="form-control filter-input" v-model="formData.business">
                  <option value="">All</option>
                  <option value="K">K-Pump</option>
                  <option value="L">L-Tools</option>
                </select>
              </div>
              <div class="filter-actions">
                <button @click.prevent="fetchReport()" class="btn btn-filter-apply">
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
              <a @click.prevent="">
                <export-excel
                    class="btn btn-tool btn-tool-excel"
                    :data="json_data"
                    :fields="json_fields"
                    worksheet="Summary Report"
                    name="summary-report.xls">
                  <i class="fas fa-file-excel"></i> Excel
                </export-excel>
              </a>
            </div>
            <div class="toolbar-right">
              <span class="record-count" v-if="data.length">
                <i class="fas fa-list"></i> {{ data.length }} records
              </span>
            </div>
          </div>

          <!-- Table Section -->
          <div class="table-section" ref="document">
            <div class="table-scroll">
              <table id="printMe" class="modern-table">
                <thead>
                  <tr>
                    <th>SL</th>
                    <th>Staff ID</th>
                    <th>Staff Name</th>
                    <th>Territory</th>
                    <th>Bank Acc No</th>
                    <th>Routing No</th>
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
                    <tr v-for="(item, index) in data" :key="index" :class="{'row-even': index % 2 === 0, 'row-odd': index % 2 !== 0}">
                      <td class="td-sl">{{ index + 1 }}</td>
                      <td>{{ item.StaffID }}</td>
                      <td class="td-name">{{ item.StaffName }}</td>
                      <td>{{ item.TTYName }}</td>
                      <td>{{ item.BankAccNo }}</td>
                      <td>{{ item.RoutingNo }}</td>
                      <td class="td-number">
                        <span class="count-badge">{{ item.TotalServices }}</span>
                      </td>
                      <td class="td-amount">{{ item.TotalServiceCharge }}</td>
                      <td class="td-amount td-cost-highlight">{{ item.TotalServiceCost }}</td>
                      <td class="td-amount">{{ item.AvgServiceCharge }}</td>
                      <td>
                        <span class="badge-business" :class="item.Business === 'K' ? 'badge-biz-k' : 'badge-biz-l'">
                          {{ item.Business === 'K' ? 'K-Pump' : item.Business === 'L' ? 'L-Tools' : item.Business }}
                        </span>
                      </td>
                      <td>
                        <span class="badge-service-type" :class="'badge-stype-' + (item.ServiceType || '').toLowerCase()">
                          {{ item.ServiceType }}
                        </span>
                      </td>
                    </tr>
                  </template>
                  <template v-else>
                    <tr>
                      <td colspan="12" class="td-empty">
                        <i class="fas fa-chart-bar"></i>
                        <p>No records found</p>
                      </td>
                    </tr>
                  </template>
                </tbody>
                <tfoot v-if="data.length > 0">
                  <tr class="total-row">
                    <td colspan="6" class="td-total-label">Grand Total</td>
                    <td class="td-total-value">{{ grandTotalServices }}</td>
                    <td class="td-total-value">{{ grandTotalCharge }}</td>
                    <td class="td-total-value">{{ grandTotalCost }}</td>
                    <td colspan="3"></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Footer Section -->
          <div class="table-footer">
            <div class="footer-info">
              <span class="text-muted" v-if="data.length">
                Total {{ data.length }} records
              </span>
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
        'Territory': 'TTYName',
        'Bank Acc No': 'BankAccNo',
        'Routing No': 'RoutingNo',
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
  computed: {
    grandTotalServices() {
      return this.data.reduce((sum, r) => sum + (parseInt(r.TotalServices) || 0), 0);
    },
    grandTotalCharge() {
      return this.data.reduce((sum, r) => sum + (parseFloat(r.TotalServiceCharge) || 0), 0).toFixed(2);
    },
    grandTotalCost() {
      return this.data.reduce((sum, r) => sum + (parseFloat(r.TotalServiceCost) || 0), 0).toFixed(2);
    }
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
      const rows = this.data;
      if (!rows.length) {
        this.$toastr.error('No data to print.');
        return;
      }

      const fromDate = moment(this.formData.fromDate);
      const toDate = moment(this.formData.toDate);
      const monthYear = fromDate.format('MMMM-YYYY');
      const dateRange = fromDate.format('DD MMM YYYY') + ' to ' + toDate.format('DD MMM YYYY');

      let businessLabel = 'All';
      if (this.formData.business === 'K') businessLabel = 'ACI K-Pump';
      else if (this.formData.business === 'L') businessLabel = 'ACI Smart Tools';

      const grandTotal = rows.reduce((sum, r) => sum + (parseFloat(r.TotalServiceCost) || 0), 0);

      let tableRows = '';
      rows.forEach((item, i) => {
        tableRows += `
          <tr>
            <td style="text-align:center;">${i + 1}</td>
            <td>${item.StaffName || ''}</td>
            <td style="text-align:center;">${item.TTYName || ''}</td>
            <td style="text-align:center;">${item.BankAccNo || ''}</td>
            <td style="text-align:center;">${item.RoutingNo || ''}</td>
            <td style="text-align:center;">${item.BankName || ''}</td>
            <td style="text-align:right;">${parseFloat(item.TotalServiceCost || 0).toLocaleString()}</td>
          </tr>`;
      });

      const html = `
        <!DOCTYPE html>
        <html>
        <head>
          <title>Service Bill - Print</title>
          <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
              font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
              padding: 30px 40px;
              color: #222;
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
            }
            .header {
              text-align: center;
              margin-bottom: 8px;
            }
            .header h1 {
              font-size: 20px;
              font-weight: 700;
              color: #1a1a2e;
              margin-bottom: 2px;
              letter-spacing: 0.5px;
            }
            .header h2 {
              font-size: 15px;
              font-weight: 600;
              color: #16213e;
              margin-bottom: 0;
            }
            .meta-row {
              display: flex;
              justify-content: space-between;
              align-items: center;
              margin-bottom: 12px;
              padding: 6px 0;
              border-bottom: 2px solid #1a1a2e;
            }
            .meta-row span {
              font-size: 12px;
              font-weight: 600;
              color: #333;
            }
            table {
              width: 100%;
              border-collapse: collapse;
              font-size: 12px;
            }
            thead th {
              background-color: #1a1a2e;
              color: #fff;
              padding: 8px 6px;
              text-align: center;
              font-weight: 600;
              font-size: 11.5px;
              border: 1px solid #1a1a2e;
            }
            tbody td {
              padding: 6px;
              border: 1px solid #ccc;
              font-size: 11.5px;
            }
            tbody tr:nth-child(even) {
              background-color: #f4f6f9;
            }
            tbody tr:hover {
              background-color: #e8ecf1;
            }
            .total-row td {
              font-weight: 700;
              font-size: 12.5px;
              background-color: #1a1a2e !important;
              color: #fff;
              padding: 8px 6px;
              border: 1px solid #1a1a2e;
            }
            .footer {
              margin-top: 30px;
              display: flex;
              justify-content: space-between;
              font-size: 11px;
              color: #666;
            }
            @media print {
              body { padding: 15px 20px; }
              tbody tr:nth-child(even) { background-color: #f4f6f9 !important; }
              thead th { background-color: #1a1a2e !important; color: #fff !important; }
              .total-row td { background-color: #1a1a2e !important; color: #fff !important; }
            }
          </style>
        </head>
        <body>
          <div class="header">
            <h1>ACI Motors LTD</h1>
            <h2>Tools Service Bill</h2>
          </div>
          <div class="meta-row">
            <span>Month: ${monthYear}</span>
            <span>Period: ${dateRange}</span>
            <span>Portfolio: ${businessLabel}</span>
          </div>
          <table>
            <thead>
              <tr>
                <th style="width:5%;">Sl No</th>
                <th style="width:22%;">Name of Technician</th>
                <th style="width:14%;">Territory</th>
                <th style="width:20%;">Bank Account</th>
                <th style="width:14%;">Routing No</th>
                <th style="width:12%;">Bank Name</th>
                <th style="width:13%;">Total</th>
              </tr>
            </thead>
            <tbody>
              ${tableRows}
              <tr class="total-row">
                <td colspan="6" style="text-align:right;">Total</td>
                <td style="text-align:right;">${grandTotal.toLocaleString()}</td>
              </tr>
            </tbody>
          </table>
          <div class="footer">
            <span>Generated on: ${moment().format('DD MMM YYYY, h:mm A')}</span>
            <span>ACI Motors LTD - Service Management System</span>
          </div>
        </body>
        </html>
      `;

      const printWindow = window.open('', '_blank');
      printWindow.document.write(html);
      printWindow.document.close();
      printWindow.onload = function () {
        printWindow.focus();
        printWindow.print();
      };
    },
    moment: function (date) {
      return moment(date);
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
  min-width: 160px;
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
.record-count {
  font-size: 13px;
  color: #6c757d;
  font-weight: 500;
}
.record-count i {
  margin-right: 5px;
  color: #adb5bd;
}

/* ====== Table Section ====== */
.table-section {
  padding: 0 20px;
  overflow-x: auto;
}
.table-scroll {
  border-radius: 8px;
  border: 1px solid #e9ecef;
  overflow: auto;
  max-height: 520px;
}
.modern-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1100px;
}
.modern-table thead th {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  color: #fff;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 10px 10px;
  border: none;
  white-space: nowrap;
  text-align: center;
  position: sticky;
  top: 0;
  z-index: 1;
}
.modern-table tbody td {
  padding: 8px 10px;
  font-size: 12px;
  color: #495057;
  vertical-align: middle;
  border-bottom: 1px solid #f0f0f0;
  transition: background-color 0.15s;
}
.modern-table tbody tr:hover td {
  background-color: #e8f4fd !important;
}
.row-even td {
  background-color: #fff;
}
.row-odd td {
  background-color: #f8f9fb;
}

/* Cell Styles */
.td-sl {
  text-align: center;
  font-weight: 600;
  color: #6c757d;
  width: 50px;
}
.td-name {
  font-weight: 500;
  color: #2c3e50;
}
.td-number {
  text-align: center;
}
.count-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 24px;
  padding: 0 8px;
  border-radius: 12px;
  background: linear-gradient(135deg, #4a90d9 0%, #74b9ff 100%);
  color: #fff;
  font-size: 11px;
  font-weight: 600;
}
.td-amount {
  text-align: right;
  font-weight: 500;
  font-family: 'Consolas', monospace;
  white-space: nowrap;
}
.td-cost-highlight {
  font-weight: 700;
  color: #2c3e50 !important;
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

/* Badges */
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
.badge-service-type {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.3px;
}
.badge-stype-regular {
  background: #dfe6e9;
  color: #2d3436;
}
.badge-stype-self {
  background: linear-gradient(135deg, #fdcb6e, #ffeaa7);
  color: #6c5c00;
}
.badge-stype-outsource {
  background: linear-gradient(135deg, #a29bfe, #6c5ce7);
  color: #fff;
}

/* Total Row */
.total-row td {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important;
  color: #fff !important;
  font-weight: 700;
  font-size: 12px;
  padding: 10px 10px;
  border: none;
}
.td-total-label {
  text-align: right;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-size: 11px;
}
.td-total-value {
  text-align: right;
  font-family: 'Consolas', monospace;
  font-size: 13px;
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
</style>

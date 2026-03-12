<template>
  <div v-if="isAuthenticate">
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h3>Outsource Service Summary Report</h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><router-link :to="{name: 'Home'}">Home</router-link></li>
                <li class="breadcrumb-item active">Outsource Service Summary Report</li>
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
                    name="outsource-summary-report.xls">
                  <i class="fas fa-file-excel"></i> Excel
                </export-excel>
              </a>
              <button class="btn btn-tool" @click.prevent="downloadWarrantyPDF('OutsourceWarrantyCardImage')">
                <i class="fas fa-id-card"></i> Warranty Cards
              </button>
              <button class="btn btn-tool" @click.prevent="downloadWarrantyPDF('OutsourceBillImage')">
                <i class="fas fa-file-image"></i> Bill Images
              </button>
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
                <td>{{ providedService.Mobile }}</td>
                <td>{{ providedService.Address }}</td>
                <td>{{ providedService.Brandname }}</td>
                <td>{{ providedService.PurchaseDate }}</td>
                <td>{{ providedService.ActionTaken }}</td>
                <td>
                  <span class="badge-business" :class="providedService.Business === 'K' ? 'badge-biz-k' : 'badge-biz-l'">
                    {{ providedService.Business === 'K' ? 'K-Pump' : providedService.Business === 'L' ? 'L-Tools' : providedService.Business }}
                  </span>
                </td>
                <td class="td-technician">{{ providedService.TechnicianName }}</td>
                <td>{{ providedService.TechnicianAddress }}</td>
                <td>{{ providedService.TechnicianMobile }}</td>
                <td class="td-action">
                  <button type="button" class="btn btn-view btn-view-wc"
                          @click="openImage(providedService.ServiceMasterID,'OutsourceWarrantyCardImage')">
                    <i class="fas fa-eye"></i> View
                  </button>
                </td>
                <td class="td-action">
                  <button type="button" class="btn btn-view btn-view-bill"
                          @click="openImage(providedService.ServiceMasterID,'OutsourceBillImage')">
                    <i class="fas fa-eye"></i> View
                  </button>
                </td>
                <td class="td-cost">
                  <div class="cost-cell">
                    <span class="cost-value">{{ providedService.OutsourceTotalCost || 0 }}</span>
                    <button type="button" class="btn btn-edit-cost" @click="openTotalCostModal(providedService)" title="Edit Total Cost">
                      <i class="fas fa-pen"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="providedServiceList.length === 0">
                <td colspan="17" class="td-empty">
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
              <pagination :pagination="pagination"
                          @prev="allService(pagination.prevPageUrl)"
                          @next="allService(pagination.nextPageUrl)">
              </pagination>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Update Total Cost Modal -->
    <div class="modal fade" id="outsourceTotalCostModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modern-modal">
          <div class="modal-header modern-modal-header">
            <div class="modal-title-wrap">
              <i class="fas fa-money-bill-wave"></i>
              <h5 class="modal-title">Update Total Cost</h5>
            </div>
            <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body modern-modal-body">
            <div class="modal-info-row">
              <div class="info-item">
                <label><i class="fas fa-hashtag"></i> Service ID</label>
                <div class="info-value">{{ editTotalCost.serviceMasterId }}</div>
              </div>
              <div class="info-item">
                <label><i class="fas fa-user"></i> Customer</label>
                <div class="info-value">{{ editTotalCost.customerName }}</div>
              </div>
            </div>
            <div class="form-group modern-form-group">
              <label><i class="fas fa-coins"></i> Total Cost Amount</label>
              <div class="input-group modern-input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text modern-prepend">BDT</span>
                </div>
                <input type="number" class="form-control modern-cost-input" v-model="editTotalCost.totalCost" min="0" step="0.01" placeholder="Enter amount">
              </div>
            </div>
          </div>
          <div class="modal-footer modern-modal-footer">
            <button type="button" class="btn btn-modal-cancel" data-dismiss="modal">
              <i class="fas fa-times"></i> Cancel
            </button>
            <button type="button" class="btn btn-modal-save" @click="updateTotalCost" :disabled="updating">
              <span v-if="updating"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
              <span v-else><i class="fas fa-check"></i> Save Changes</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <ImageModal/>
  </div>
  <div class="col-md-12 mb-3 mt-3" v-else>Please Login</div>
</template>

<script>
import DataTable from '../datatable/DataTable';
import Pagination from '../pagination/Pagination';
import moment from 'moment'
import html2pdf from 'html2pdf.js'
import ImageModal from './ImageModal'
import {bus} from '../../app'
import {jsPDF} from "jspdf";

export default {
  components: {
    ImageModal,
    'data-table': DataTable,
    'pagination': Pagination,
  },
  data:() => {
    let sortOrders = {};
    let columns = [
      {width: '60px', label:'SL', name:'ServiceMasterID'},
      {width: '80px', label:'StaffID', name:'StaffID'},
      {width: '120px', label:'StaffName', name:'UserName'},
      {width: '100px', label:'Territory', name:'Territory'},
      {width: '130px', label:'CustomerName', name:'CustomerName'},
      {width: '110px', label:'CustomerMobile', name:'CustomerMobile'},
      {width: '130px', label:'Address', name:'Address'},
      {width: '100px', label:'ModelName', name:'Model'},
      {width: '95px', label:'PurchaseDate', name:'PurchaseDate'},
      {width: '110px', label:'ActionTaken', name:'Action'},
      {width: '80px', label:'Business', name:'Business'},
      {width: '120px', label:'TechnicianName', name:'TechnicianName'},
      {width: '130px', label:'TechnicianAddress', name:'TechnicianAddress'},
      {width: '110px', label:'TechnicianMobile', name:'TechnicianMobile'},
      {width: '80px', label: 'WCImage', name: 'WCImage'},
      {width: '80px', label: 'BillImage', name: 'BillImage'},
      {width: '100px', label: 'TotalCost', name: 'TotalCost'},
    ];
    columns.forEach((column) => {
      sortOrders[column.name] = 1;
    });
    return {
      base_url: '/waterpump/public',
      allImage: [],
      imgWidth: 80,
      imgHeight: 80,
      xAxis: 15,
      yAxis: 40,
      textXAxis: 30,
      textYAxis: 35,
      providedServiceList: [],
      columns:columns,
      sortKey: 'StaffID',
      sortOrders:sortOrders,
      perPage: ['50', '100', '200'],
      tableData:{
        draw:0,
        length:50,
        search:'',
        column: 0,
        dir:'desc',
        fromDate: '',
        toDate: '',
        serviceTime: '',
        business: ''
      },
      pagination:{
        lastPage:'',
        currentPage:'',
        total:'',
        lastPageUrl:'',
        nextPageUrl:'',
        prevPageUrl:'',
        from:'',
        to:''
      },
      isAuthenticate: '',
      loader: false,
      opened: [],
      expand: false,
      masterID: {
        serviceMasterId:''
      },
      json_fields: {
        'SL': 'ServiceMasterID',
        'Staff ID': 'StaffID',
        'Staff Name': 'StaffName',
        'Territory': 'TTYName',
        'Customer Name': 'CustomerName',
        'Customer Mobile': 'Mobile',
        'Address': 'Address',
        'Model': 'Brandname',
        'Purchase Date': 'PurchaseDate',
        'Action': 'ActionTaken',
        'Technician Name': 'TechnicianName',
        'Technician Address': 'TechnicianAddress',
        'Technician Mobile': 'TechnicianMobile',
        'Total Cost': 'TotalCost',
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
      editTotalCost: {
        serviceMasterId: '',
        customerName: '',
        totalCost: 0
      },
      updating: false,
    };
  },
  created(){
    if (localStorage.getItem('auth') != null) {
      this.isAuthenticate = true;
      this.userId = this.$store.state.userid;
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.$store.state.token;
      this.allService();
    }else{
      this.isAuthenticate = false;
      this.$router.push(this.base_url+"/login");
    }
  },
  mounted() {
    this.loader = true;
  },
  methods: {
    allService(url=this.base_url+"/api/admindashboard/providedoutsourceservicereport") {
      this.tableData.draw++;
      axios
          .get(url, { params: this.tableData }, { token: this.$store.state.token })
          .then((response) => {
            this.loader = false;
            if(this.tableData.draw == response.data.serviceMaster.draw){
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
    openImage(id,type) {
      bus.$emit('imageModalShow', id, type);
    },
    configPagination(data){
      this.pagination.lastPage = data.last_page;
      this.pagination.currentPage = data.current_page;
      this.pagination.total = data.total;
      this.pagination.lastPage = data.last_page_url;
      this.pagination.nextPageUrl = data.next_page_url;
      this.pagination.prevPageUrl = data.prev_page_url;
      this.pagination.from = data.from;
      this.pagination.to = data.to;
    },
    sortBy(key){
      this.sortKey = key;
      this.sortOrders[key] = this.sortOrders[key] * -1;
      this.tableData.column = this.getIndex(this.columns, 'name', key);
      this.tableData.dir = this.sortOrders[key] === 1 ? 'asc' : 'desc';
      this.allService();
    },
    getIndex(array, key, value){
      return array.findIndex(i => i[key] == value)
    },
    toggle(id) {
      const index = this.opened.indexOf(id);
      if (index > -1) {
        this.opened.splice(index, 1)
      } else {
        this.opened.splice(index, 1)
        this.opened.push(id)
        if(id != 0){
          this.masterID.serviceMasterId = id;
          axios
              .get(this.base_url+'/api/admindashboard/providedserviceDetails', { params: this.masterID }, { token: this.$store.state.token })
              .then((response) => {
                if(response.data.status == true){
                  this.serviceDetails = response.data.serviceDetails;
                }
              })
              .catch((error) => {
                this.$toastr.error('Something went wrong.');
              });
        }
      }
    },
    toggleIcon(img){
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
    print () {
      this.$htmlToPaper('printMe', null, () => {
      });
    },
    openTotalCostModal(service) {
      this.editTotalCost.serviceMasterId = service.ServiceMasterID;
      this.editTotalCost.customerName = service.CustomerName;
      this.editTotalCost.totalCost = service.OutsourceTotalCost || 0;
      $('#outsourceTotalCostModal').modal('show');
    },
    updateTotalCost() {
      this.updating = true;
      axios.post(this.base_url + '/api/admindashboard/updateOutsourceServiceTotalCost', {
        serviceMasterId: this.editTotalCost.serviceMasterId,
        totalCost: this.editTotalCost.totalCost
      }, {token: this.$store.state.token})
      .then((response) => {
        this.updating = false;
        if (response.data.status === true) {
          this.$toastr.success(response.data.message);
          $('#outsourceTotalCostModal').modal('hide');
          this.allService();
        } else {
          this.$toastr.error(response.data.message);
        }
      })
      .catch((error) => {
        this.updating = false;
        this.$toastr.error('Something went wrong.');
      });
    },
    exportReport(){
      axios.get(this.base_url+'/api/admindashboard/exportOutsourceServiceReport', { params: this.tableData }, { token: this.$store.state.token }).then((response) => {
        this.json_data = [];
        this.json_data = response.data;
      }).catch((error) => {
        this.$toastr.error('Something went wrong.');
      });
    },
    moment: function (date) {
      return moment(date);
    },
    exportToPDF () {
      html2pdf(this.$refs.document, {
        margin: 1,
        filename: 'outsource-summary-report.pdf',
        image: { type: 'jpeg', quality: 1 },
        html2canvas:  { scale: 2 },
        pagebreak: {mode: ['avoid-all', 'css', 'legacy']},
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape',
          putOnlyUsedFonts:true, floatPrecision: 12, compress: true, fontSize:8 }
      })
    },
    downloadWarrantyPDF(imageType) {
      this.allImage = [];
      let config = {
        header: {
          Authorization: 'bearer ' + this.$store.state.token
        }
      };
      let remote_url = 'https://app.acibd.com/apps/waterpump/public/uploads/';
      let instance = this;
      axios.post(this.base_url + '/api/admindashboard/get-image-pdf', {
        'tableData': instance.tableData,
        'imageType': imageType
      }, config)
          .then(function (response) {
            var currentdate = new Date();
            var fileExt = `${currentdate.getDate()}${currentdate.getMonth()}${currentdate.getFullYear()}`;
            let imageData = [];
            var doc = new jsPDF();
            let img,titleId;
            response.data.data.forEach((item, index) => {
              if (response.data.imageType === 'OutsourceWarrantyCardImage') {
                img = `${remote_url}${item.WarrentyCardImage}`;
                titleId = item.ServiceMasterId;
              } else if (response.data.imageType === 'OutsourceBillImage') {
                img = `${remote_url}${item.Image}`;
                titleId = item.ServiceMasterId
              }

              imageData.push({
                imageText: titleId,
                itemImage: img
              });
            });

            if (response.data.imageType === 'OutsourceWarrantyCardImage') {
              doc.text('OUTSOURCE SERVICE WARRANTY CARDS', 65, 25);
            } else if (response.data.imageType === 'OutsourceBillImage') {
              doc.text('OUTSOURCE SERVICE BILL IMAGES', 65, 25);
            }

            for (var index = 0; index <= imageData.length;index = index + 4) {
              if (index < imageData.length && imageData.length > 4) {
                if (imageData[index] !== undefined) {
                  doc.text('SN: ' + imageData[index].imageText, instance.textXAxis, instance.textYAxis);
                  doc.addImage(imageData[index].itemImage, 'JPEG', instance.xAxis, instance.yAxis, 80, 80);
                }
                if (imageData[index+1] !== undefined) {
                  doc.text('SN: ' + imageData[index+1].imageText, instance.textXAxis + 100, instance.textYAxis);
                  doc.addImage(imageData[index+1].itemImage, 'JPEG', instance.xAxis + 100, instance.yAxis, 80, 80);
                }
                if (imageData[index+3] !== undefined) {
                  doc.text('SN: ' + imageData[index+2].imageText, instance.textXAxis, instance.textYAxis + 100);
                  doc.addImage(imageData[index+2].itemImage, 'JPEG', instance.xAxis, instance.yAxis + 100, 80, 80);
                }
                if (imageData[index+4] !== undefined) {
                  doc.text('SN: ' + imageData[index+3].imageText, instance.textXAxis + 100, instance.textYAxis + 100);
                  doc.addImage(imageData[index+3].itemImage, 'JPEG', instance.xAxis + 100, instance.yAxis + 100, 80, 80);
                }
                if (index + 4 < imageData.length) {
                  doc.addPage();
                } else {
                  doc.save(`${fileExt}-${response.data.imageType}`);
                }
              } else if (imageData.length > 0 && imageData.length <= 4) {
                if (imageData[index] !== undefined) {
                  doc.text('SN: ' + imageData[index].imageText, instance.textXAxis, instance.textYAxis);
                  doc.addImage(imageData[index].itemImage, 'JPEG', instance.xAxis, instance.yAxis, 80, 80);
                }
                if (imageData[index+1] !== undefined) {
                  doc.text('SN: ' + imageData[index+1].imageText, instance.textXAxis + 100, instance.textYAxis);
                  doc.addImage(imageData[index+1].itemImage, 'JPEG', instance.xAxis + 100, instance.yAxis, 80, 80);
                }
                if (imageData[index+3] !== undefined) {
                  doc.text('SN: ' + imageData[index+2].imageText, instance.textXAxis, instance.textYAxis + 100);
                  doc.addImage(imageData[index+2].itemImage, 'JPEG', instance.xAxis, instance.yAxis + 100, 80, 80);
                }
                if (imageData[index+4] !== undefined) {
                  doc.text('SN: ' + imageData[index+3].imageText, instance.textXAxis + 100, instance.textYAxis + 100);
                  doc.addImage(imageData[index+3].itemImage, 'JPEG', instance.xAxis + 100, instance.yAxis + 100, 80, 80);
                }
                doc.save(`${fileExt}-${response.data.imageType}`);
              } else {
                doc.save(`${fileExt}-${response.data.imageType}`);
              }
            }
          }).catch(function (error) {
      });
    },
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
  overflow-x: auto;
}
.table-section >>> .tableFixHead {
  border-radius: 8px;
  border: 1px solid #e9ecef;
  overflow: auto;
  height: auto;
  max-height: 520px;
}
.table-section >>> table {
  margin-bottom: 0;
  min-width: 1600px;
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
.td-technician {
  font-weight: 500;
  color: #6c5ce7 !important;
}
.td-action {
  text-align: center;
}
.td-cost {
  text-align: center;
  min-width: 120px;
  white-space: nowrap;
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

/* View Buttons */
.btn-view {
  border: none;
  border-radius: 5px;
  padding: 4px 12px;
  font-size: 11px;
  font-weight: 500;
  transition: all 0.2s;
  color: #fff;
}
.btn-view i {
  margin-right: 3px;
}
.btn-view-wc {
  background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
}
.btn-view-wc:hover {
  background: linear-gradient(135deg, #5b4cdb 0%, #8c85f0 100%);
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(108, 92, 231, 0.3);
  color: #fff;
}
.btn-view-bill {
  background: linear-gradient(135deg, #00b894 0%, #55efc4 100%);
}
.btn-view-bill:hover {
  background: linear-gradient(135deg, #00a381 0%, #40d4ae 100%);
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(0, 184, 148, 0.3);
  color: #fff;
}

/* Business Badges */
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

/* Cost Cell */
.cost-cell {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.cost-value {
  font-weight: 600;
  color: #2c3e50;
  font-size: 13px;
}
.btn-edit-cost {
  width: 26px;
  height: 26px;
  padding: 0;
  border-radius: 50%;
  background: linear-gradient(135deg, #fd79a8 0%, #e84393 100%);
  color: #fff;
  border: none;
  font-size: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.btn-edit-cost:hover {
  transform: scale(1.15);
  box-shadow: 0 2px 8px rgba(232, 67, 147, 0.4);
  color: #fff;
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

/* ====== Modal ====== */
.modern-modal {
  border: none;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
}
.modern-modal-header {
  background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
  border: none;
  padding: 16px 20px;
}
.modal-title-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}
.modal-title-wrap i {
  color: #55efc4;
  font-size: 18px;
}
.modern-modal-header .modal-title {
  color: #fff;
  font-size: 16px;
  font-weight: 600;
}
.modern-close {
  color: #fff;
  opacity: 0.7;
  text-shadow: none;
  font-size: 22px;
}
.modern-close:hover {
  opacity: 1;
  color: #fff;
}
.modern-modal-body {
  padding: 24px 20px;
}
.modal-info-row {
  display: flex;
  gap: 16px;
  margin-bottom: 20px;
}
.info-item {
  flex: 1;
  background: #f8f9fa;
  border-radius: 8px;
  padding: 12px 14px;
  border: 1px solid #e9ecef;
}
.info-item label {
  display: block;
  font-size: 11px;
  font-weight: 600;
  color: #6c757d;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}
.info-item label i {
  margin-right: 4px;
  color: #adb5bd;
}
.info-value {
  font-size: 14px;
  font-weight: 600;
  color: #2c3e50;
}
.modern-form-group label {
  font-size: 13px;
  font-weight: 600;
  color: #495057;
  margin-bottom: 6px;
}
.modern-form-group label i {
  margin-right: 5px;
  color: #6c757d;
}
.modern-prepend {
  background: linear-gradient(135deg, #4a90d9 0%, #357abd 100%);
  color: #fff;
  border: none;
  font-weight: 600;
  font-size: 13px;
  border-radius: 6px 0 0 6px;
}
.modern-cost-input {
  border-radius: 0 6px 6px 0;
  border: 1.5px solid #dee2e6;
  font-size: 16px;
  font-weight: 600;
  padding: 10px 14px;
  color: #2c3e50;
}
.modern-cost-input:focus {
  border-color: #4a90d9;
  box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.15);
}
.modern-modal-footer {
  border-top: 1px solid #e9ecef;
  padding: 14px 20px;
  background: #f8f9fa;
}
.btn-modal-cancel {
  background: #fff;
  border: 1.5px solid #dee2e6;
  border-radius: 8px;
  padding: 8px 20px;
  font-size: 13px;
  font-weight: 500;
  color: #6c757d;
  transition: all 0.2s;
}
.btn-modal-cancel:hover {
  background: #f1f1f1;
  border-color: #adb5bd;
}
.btn-modal-save {
  background: linear-gradient(135deg, #4a90d9 0%, #357abd 100%);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 8px 24px;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s;
}
.btn-modal-save:hover {
  background: linear-gradient(135deg, #357abd 0%, #2868a8 100%);
  transform: translateY(-1px);
  box-shadow: 0 3px 10px rgba(53, 122, 189, 0.3);
  color: #fff;
}
.btn-modal-save:disabled {
  opacity: 0.7;
  transform: none;
  box-shadow: none;
}
</style>

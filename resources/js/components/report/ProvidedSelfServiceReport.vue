<template>
  <div v-if="isAuthenticate">
    <div class="content-wrapper">
      <section class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h3>Self-Service Summary Report</h3>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">
                  <router-link :to="{name: 'Home'}">Home</router-link>
                </li>
                <li class="breadcrumb-item active">Self-Service Summary Report</li>
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
            <form class="form-inline">
              <div class="form-group">
                <p class="form-control-static">From Date</p>
              </div>
              <div class="form-group mx-sm-3">
                <input class="form-control" type="date" v-model="tableData.fromDate">
              </div>
              <div class="form-group">
                <p class="form-control-static">To Date</p>
              </div>
              <div class="form-group mx-sm-3">
                <input class="form-control" type="date" v-model="tableData.toDate">
              </div>
              <div class="form-group">
                <p class="form-control-static">Service Time</p>
              </div>
              <div class="form-group mx-sm-3">
                <select class="form-control" v-model="tableData.serviceTime">
                  <option value="">All</option>
                  <option value="First">First</option>
                  <option value="Second">Second</option>
                  <option value="Third">Third</option>
                  <option value="Fourth">Fourth</option>
                </select>
              </div>
              <div class="form-group">
                <button type="submit" @click.prevent="allService()" class="btn btn-default button-border"><i
                    class="fas fa-filter"></i> Filter
                </button>
              </div>
              <div class="form-group mx-sm-3">
                <button type="submit" @click.prevent="clearFilter()" class="btn btn-default button-border">Clear
                  Filter
                </button>
              </div>
            </form>
          </div>
          <div class="col-md-12">
            <div class="input-group">
              <div class="col-md-8" style="padding-left: 0px;">
                <button class="btn btn-default button-border" @click.prevent="print"><i class="fas fa-print"></i> Print
                </button>
                <a @click.prevent="exportReport">
                  <export-excel
                      class="btn btn-default button-border"
                      :data="json_data"
                      :fields="json_fields"
                      worksheet="My Worksheet"
                      name="self-service-report.xls">
                    Excel
                  </export-excel>
                </a>
                <button class="btn btn-default button-border" @click.prevent="downloadWarrantyPDF('selfWarrantyImage')">
                  <i class="fas fa-print"></i> Download Warranty Cards
                </button>
                <button class="btn btn-default button-border" @click.prevent="downloadWarrantyPDF('SelfBillImage')">
                  <i class="fas fa-print"></i> Download Bill Images
                </button>
                <!-- <button class="btn btn-default button-border" @click.prevent="exportToPDF">PDF</button> -->
              </div>

              <div class="col-md-4 input-group mb-3" style="padding-right: 0px;">
                <div class="input-group-prepend">
                  <span class="input-group-text" id="basic-addon1">Search</span>
                </div>
                <input class="form-control" type="text" v-model="tableData.search" @input="allService()">
              </div>
            </div>
            <div ref="document">
              <data-table :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" @sort="sortBy" id="printMe">
                <tbody>
                <tr v-for="providedService in providedServiceList" :key="providedService.ServiceMasterID">
                  <td>{{ providedService.ServiceMasterID }}</td>
                  <td>{{ providedService.StaffID }}</td>
                  <td>{{ providedService.StaffName }}</td>
                  <td>{{ providedService.TTYName }}</td>
                  <td>{{ providedService.CustomerName }}</td>
                  <td>{{ providedService.Mobile }}</td>
                  <td>{{ providedService.Address }}</td>
                  <td>{{ providedService.Brandname }}</td>
                  <td>{{ providedService.PurchaseDate }}</td>
                  <td>{{ providedService.ActionTaken }}</td>
                  <td>
                    <button type="button" class="btn btn-primary"
                            @click="openImage(providedService.ServiceMasterID,'SelfWarrantyCardImage')">
                      View
                    </button>
                  </td>
                  <td>
                    <button type="button" class="btn btn-primary"
                            @click="openImage(providedService.ServiceMasterID,'SelfBillImage')">View
                    </button>
                  </td>
                  <td>
                    {{ providedService.SelfTotalCost }}
                  </td>
                </tr>
                </tbody>
              </data-table>
            </div>
          </div>
          <div class="col-md-12 input-group mb-3 mt-3">
            <div class="col-md-6 invisible">
              <div class="col-md-4 input-group-prepend justify-content-center">
                <span class="mt-2">Show:</span>&nbsp;<select class="form-control" v-model="tableData.length"
                                                             @change="allService()">
                <option v-for="(records, index) in perPage" :key="index" :value="records">{{ records }}</option>
              </select>
              </div>
            </div>
            <div class="col-md-6 d-flex flex-row-reverse">
              <pagination :pagination="pagination"
                          @prev="allService(pagination.prevPageUrl)"
                          @next="allService(pagination.nextPageUrl)">
              </pagination>
            </div>
          </div>
        </div>
      </section>
    </div>
    <ImageModal/>
  </div>
  <div class="col-md-12 mb-3 mt-3" v-else>Please Login</div>
</template>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
import DataTable from '../datatable/DataTable';
import Pagination from '../pagination/Pagination';
import moment from 'moment'
import html2pdf from 'html2pdf.js'
import ImageModal from './ImageModal'
import {bus} from '../../app'
import {jsPDF} from 'jspdf'

export default {
  components: {
    ImageModal,
    'data-table': DataTable,
    'pagination': Pagination,
  },
  data: () => {
    let sortOrders = {};
    let columns = [
      {width: '20%', label: 'SL', name: 'ServiceMasterID'},
      {width: '20%', label: 'StaffID', name: 'StaffID'},
      {width: '20%', label: 'StaffName', name: 'UserName'},
      {width: '20%', label: 'Territory', name: 'Territory'},
      {width: '20%', label: 'CustomerName', name: 'CustomerName'},
      {width: '20%', label: 'CustomerMobile', name: 'CustomerMobile'},
      {width: '20%', label: 'Address', name: 'Address'},
      {width: '20%', label: 'ModelName', name: 'Model'},
      {width: '20%', label: 'PurchaseDate', name: 'PurchaseDate'},
      {width: '20%', label: 'ActionTaken', name: 'Action'},
      {width: '20%', label: 'WCImage', name: 'WCImage'},
      {width: '20%', label: 'BillImage', name: 'BillImage'},
      {width: '20%', label: 'TotalCost', name: 'TotalCost'},
    ];
    columns.forEach((column) => {
      sortOrders[column.name] = 1;
    });
    return {
      base_url: '/waterpump/public',
      perPageImage: 0,
      allImage: [],
      imgWidth: 80,
      imgHeight: 80,
      xAxis: 15,
      yAxis: 40,
      textXAxis: 30,
      textYAxis: 35,
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
        serviceTime: ''
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
        'Customer Mobile': 'Mobile',
        'Address': 'Address',
        'Model': 'Brandname',
        'Purchase Date': 'PurchaseDate',
        'Action': 'ActionTaken',
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
    };
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
    allService(url = this.base_url + "/api/admindashboard/providedselfservicereport") {
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
    openImage(id, type) {
      bus.$emit('imageModalShow', id, type);
    },
    createPDF(imgData, serviceId, width, height) {
      this.allImage.push({img: imgData, width: width, height: height});
      setTimeout(() => {
        var doc = new jsPDF('p', 'mm', [297, 210]);
        this.allImage = this.allImage.sort((a, b) => {
          return a.order - b.order;
        })
        this.allImage.forEach((image, index) => {
          let width = image.width * 0.2645 > 190 ? 190 : image.width * 0.2645;
          let height = image.height * 0.2645 > 277 ? 277 : image.height * 0.2645;
          let yMargin = (297 - height) / 2;
          let xMargin = (210 - width) / 2;
          doc.text('SN: ' + serviceId);
          doc.addImage(image.img, 'JPEG', xMargin, yMargin, width, height);
          if (index + 1 < this.allImage.length) doc.addPage();
        })
        doc.save("warranty_type.pdf");
      }, 500)
    },
    getImageFromUrl(url, serviceId) {
      let img = new Image();
      let instance = this;
      img.onError = function () {
        alert('Cannot load image: "' + url + '"');
      };
      img.src = url;
      img.onload = function () {
        var doc = new jsPDF('p', 'mm', [297, 210]);
        doc.text('SN: ' + serviceId, 40, 35);
        doc.addImage(image.img, 'JPEG', 15, 40, 80, 80);
        this.allImage.forEach((image, index) => {
          let width = image.width * 0.2645 > 190 ? 190 : image.width * 0.2645;
          let height = image.height * 0.2645 > 277 ? 277 : image.height * 0.2645;
          let yMargin = (297 - height) / 2;
          let xMargin = (210 - width) / 2;
          doc.text('SN: ' + serviceId);
          doc.addImage(image.img, 'JPEG', xMargin, yMargin, width, height);
          if (index + 1 < this.allImage.length) doc.addPage();
        })
        doc.save("warranty_type.pdf");
      };
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
              if (response.data.imageType === 'selfWarrantyImage') {
                img = `${remote_url}${item.WarrentyCardImage}`;
                titleId = item.ServiceMasterId;
              } else if (response.data.imageType === 'SelfBillImage') {
                img = `${remote_url}${item.Image}`;
                titleId = item.ServiceMasterId
              }

              imageData.push({
                imageText: titleId,
                itemImage: img
              });
            });

            if (response.data.imageType === 'selfWarrantyImage') {
              doc.text('SELF SERVICE WARRANTY CARDS', 65, 25);
            } else if (response.data.imageType === 'SelfBillImage') {
              doc.text('SELF SERVICE BILL IMAGES', 65, 25);
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
            // imageData.forEach((item, index) => {
            //
            // });
          }).catch(function (error) {
      });
    },

    configPagination(data) {
      this.pagination.lastPage = data.last_page;
      this.pagination.currentPage = data.current_page;
      this.pagination.total = data.total;
      this.pagination.lastPage = data.last_page_url;
      this.pagination.nextPageUrl = data.next_page_url;
      this.pagination.prevPageUrl = data.prev_page_url;
      this.pagination.from = data.from;
      this.pagination.to = data.to;
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
      this.allService();
      this.exportReport();
    },
    print() {
      // Pass the element id here
      this.$htmlToPaper('printMe', null, () => {
      });
    },
    exportReport() {
      axios.get(this.base_url + '/api/admindashboard/exportSelfServiceReport', {params: this.tableData}, {token: this.$store.state.token}).then((response) => {
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
        filename: 'self-service-report.pdf',
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
.button-border {
  border-color: #0042ff
}
</style>

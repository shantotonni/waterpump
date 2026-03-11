<template>
<div v-if="isAuthenticate">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h3>Employee List</h3>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><router-link :to="{ name: 'Home' }">Home</router-link></li>
              <li class="breadcrumb-item active">Employee List</li>
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
        <p class="mt-2 text-muted">Loading employees...</p>
      </div>

      <div class="modern-card" v-else>
        <!-- Toolbar Section -->
        <div class="toolbar-section">
          <div class="toolbar-left">
            <button class="btn btn-create" @click="createUser()">
              <i class="fas fa-plus"></i> Create New Employee
            </button>
          </div>
          <div class="toolbar-right">
            <div class="search-box">
              <i class="fas fa-search search-icon"></i>
              <input class="form-control search-input" type="text" placeholder="Search employees..." v-model="tableData.search" @input="userList()">
            </div>
          </div>
        </div>

        <!-- Table Section -->
        <div class="table-section">
          <data-table :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" @sort="sortBy">
            <tbody>
              <tr v-for="(user, index) in usersList" :key="user.UserID" :class="{'row-even': index % 2 === 0, 'row-odd': index % 2 !== 0}">
                <td class="td-action">
                  <div class="action-group">
                    <button v-if="user.active=='Y'" class="btn btn-action btn-lock" @click.prevent="lockUser(user.StaffID, false)" title="Lock User">
                      <i class="fas fa-lock"></i>
                    </button>
                    <button v-else class="btn btn-action btn-unlock" @click.prevent="lockUser(user.StaffID, true)" title="Unlock User">
                      <i class="fas fa-lock-open"></i>
                    </button>
                    <button class="btn btn-action btn-edit" @click.prevent="editUser(user)" title="Edit User">
                      <i class="fas fa-pen"></i>
                    </button>
                  </div>
                </td>
                <td class="td-id">{{user.UserID}}</td>
                <td class="td-staffid">{{user.StaffID}}</td>
                <td class="td-name">{{user.UserName}}</td>
                <td>{{user.TerritoryName}}</td>
                <td>{{user.DistrictName}}</td>
                <td>{{user.MobileNo}}</td>
                <td class="td-point">
                  <span class="point-badge" :class="user.TotalPoint > 0 ? 'point-has' : 'point-zero'">
                    {{ user.TotalPoint != null ? parseFloat(user.TotalPoint) : 0 }}
                  </span>
                </td>
                <td>
                  <span class="badge-usertype" :class="'badge-ut-' + (user.UserType || '').toLowerCase()">
                    {{user.UserTypeName}}
                  </span>
                </td>
                <td>
                  <span v-if="user.active=='Y'" class="status-badge status-active">
                    <i class="fas fa-check-circle"></i> Active
                  </span>
                  <span v-else class="status-badge status-inactive">
                    <i class="fas fa-times-circle"></i> Inactive
                  </span>
                </td>
              </tr>
              <tr v-if="usersList.length === 0">
                <td colspan="10" class="td-empty">
                  <i class="fas fa-users-slash"></i>
                  <p>No employees found</p>
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
                    @prev="userList(pagination.prevPageUrl)"
                    @next="userList(pagination.nextPageUrl)">
            </pagination>
          </div>
        </div>
      </div>

      <!-- Create / Edit User Modal -->
      <div ref="modal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content modern-modal">
            <div class="modal-header modern-modal-header">
              <div class="modal-title-wrap">
                <i class="fas" :class="isInsert ? 'fa-user-plus' : 'fa-user-edit'"></i>
                <h5 class="modal-title" v-if="isInsert">Create New Employee</h5>
                <h5 class="modal-title" v-else>Update Employee</h5>
              </div>
              <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form @submit.prevent="saveUser" @keydown="form.onKeydown($event)">
              <div class="modal-body modern-modal-body">
                <div class="required-note">
                  <i class="fas fa-info-circle"></i> Fields marked with <span class="required-field">*</span> are required
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group modern-form-group">
                      <label for="username"><span class="required-field">*</span> Name</label>
                      <input type="text" class="form-control modern-input" id="username" name="username" v-model="form.username" placeholder="Enter Staff Name"
                        :class="{ 'is-invalid': form.errors.has('username') }">
                      <has-error :form="form" field="username"></has-error>
                    </div>
                    <div class="form-group modern-form-group">
                      <label for="staffid"><span class="required-field">*</span> Staff ID</label>
                      <input type="text" class="form-control modern-input" id="staffid" name="staffid" v-model="form.staffid" placeholder="This will be the userid"
                        :class="{ 'is-invalid': form.errors.has('staffid') }" v-if="isInsert">
                      <input type="text" class="form-control modern-input" id="staffid" name="staffid" v-model="form.staffid" placeholder="This will be the userid"
                        :class="{ 'is-invalid': form.errors.has('staffid') }" v-else readonly>
                      <has-error :form="form" field="staffid"></has-error>
                    </div>
                    <div class="form-group modern-form-group" v-if="isInsert">
                      <label for="password"><span class="required-field">*</span> Password</label>
                      <input type="text" class="form-control modern-input" id="password" name="password" v-model="form.password" placeholder="Password"
                        :class="{ 'is-invalid': form.errors.has('password') }">
                      <has-error :form="form" field="password"></has-error>
                    </div>
                    <div class="form-group modern-form-group">
                      <label for="mobile"><span class="required-field">*</span> Mobile No.</label>
                      <input type="text" class="form-control modern-input" id="mobile" name="mobile" v-model="form.mobile" placeholder="Mobile Number"
                        :class="{ 'is-invalid': form.errors.has('mobile') }">
                      <has-error :form="form" field="mobile"></has-error>
                    </div>
                    <div class="form-group modern-form-group">
                      <label for="dob">Date of Birth</label>
                      <input type="date" class="form-control modern-input" id="dob" name="dob" v-model="form.dob" placeholder="Date of Birth"
                        :class="{ 'is-invalid': form.errors.has('dob') }">
                      <has-error :form="form" field="dob"></has-error>
                    </div>
                    <div class="form-group modern-form-group">
                      <label for="bank_acc_no">Bank Account Number</label>
                      <input type="text" class="form-control modern-input" id="bank_acc_no" name="bank_acc_no" v-model="form.bank_acc_no" placeholder="Bank Account Number"
                        :class="{ 'is-invalid': form.errors.has('bank_acc_no') }">
                      <has-error :form="form" field="bank_acc_no"></has-error>
                    </div>
                    <div class="form-group modern-form-group">
                      <label for="usertype"><span class="required-field">*</span> User Type</label>
                      <select name="usertype" id="usertype" v-model="form.usertype" class="form-control modern-input"
                        :class="{ 'is-invalid': form.errors.has('usertype') }">
                        <option value="" disabled>Select UserType</option>
                        <option value="A">Admin</option>
                        <option value="U" selected>User</option>
                        <option value="CC">Customer Care</option>
                      </select>
                      <has-error :form="form" field="usertype"></has-error>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group modern-form-group">
                      <label for="address"><span class="required-field">*</span> Address</label>
                      <input type="text" class="form-control modern-input" id="address" name="address" v-model="form.address" placeholder="Address"
                        :class="{ 'is-invalid': form.errors.has('address') }">
                      <has-error :form="form" field="address"></has-error>
                    </div>
                    <div class="form-group modern-form-group">
                      <label for="nid">NID</label>
                      <input type="text" class="form-control modern-input" id="nid" name="nid" v-model="form.nid" placeholder="NID"
                        :class="{ 'is-invalid': form.errors.has('nid') }">
                      <has-error :form="form" field="nid"></has-error>
                    </div>
                    <div class="form-group modern-form-group">
                      <label for="territorycode"><span class="required-field">*</span> Territory</label>
                      <select name="territorycode" id="territorycode" v-model="form.territorycode" class="form-control modern-input"
                        :class="{ 'is-invalid': form.errors.has('territorycode') }">
                        <option value="" selected disabled>Select Territory</option>
                        <option v-for="(territory, index) in territoryList" :key="index" :value="territory.TTYCode">{{territory.TTYName}}</option>
                      </select>
                      <has-error :form="form" field="territorycode"></has-error>
                    </div>
                    <div class="form-group modern-form-group">
                      <label for="districtcode"><span class="required-field">*</span> District</label>
                      <select name="districtcode" id="districtcode" v-model="form.districtcode" class="form-control modern-input" :searchable="true"
                        :class="{ 'is-invalid': form.errors.has('districtcode') }">
                        <option value="" selected disabled>Select District</option>
                        <option v-for="(district, index) in districtList" :key="index" :value="district.DistrictCode">{{district.DistrictName}}</option>
                      </select>
                      <has-error :form="form" field="districtcode"></has-error>
                    </div>
                    <div class="form-group modern-form-group">
                      <label for="bank_name">Bank Name</label>
                      <input type="text" class="form-control modern-input" id="bank_name" name="bank_name" v-model="form.bank_name" placeholder="Bank Name"
                        :class="{ 'is-invalid': form.errors.has('bank_name') }">
                      <has-error :form="form" field="bank_name"></has-error>
                    </div>
                    <div class="form-group modern-form-group">
                      <label for="routing_no">Routing Number</label>
                      <input type="text" class="form-control modern-input" id="routing_no" name="routing_no" v-model="form.routing_no" placeholder="Routing Number"
                        :class="{ 'is-invalid': form.errors.has('routing_no') }">
                      <has-error :form="form" field="routing_no"></has-error>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer modern-modal-footer">
                <button type="button" class="btn btn-modal-cancel" data-dismiss="modal">
                  <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-modal-save" :disabled="form.busy" v-if="isInsert">
                  <i class="fas fa-save"></i> Save Employee
                </button>
                <button type="submit" class="btn btn-modal-save" :disabled="form.busy" v-else>
                  <i class="fas fa-check"></i> Save Changes
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </section>
  </div>
</div>
<div v-else>Please Login</div>
</template>

<script>
import DataTable from '../datatable/DataTable';
import Pagination from '../pagination/Pagination';
export default {
    components: {
        'data-table': DataTable,
        'pagination': Pagination,
    },
    data: () => {
      let sortOrders = {};
      let columns = [
        {label:'Action', name:'Edit'},
        {label:'SL', name:'id'},
        {label:'Staff ID', name:'staffid'},
        {label:'Staff Name', name:'username'},
        {label:'Territory', name:'territory'},
        {label:'District Name', name:'district'},
        {label:'Mobile No.', name:'mobile'},
        {label:'Avg. Rating Point', name:'points'},
        {label:'User Type', name:'usertype'},
        {label:'Status', name:'status'},
      ];
      columns.forEach((column) => {
          sortOrders[column.name] = 1;
      });
    return {
    base_url: '/waterpump/public',
      usersList: [],
      districtList: [],
      territoryList: [],
      columns:columns,
      sortKey: 'staffid',
      sortOrders:sortOrders,
      perPage: ['10', '20', '30'],
      tableData:{
          draw:0,
          length:10,
          search:'',
          column: 0,
          dir:'desc',
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
        form: new Form ({
            username: '',
            staffid: '',
            password: '',
            mobile: '',
            dob: "",
            address: '',
            nid: '',
            bank_name: '',
            bank_acc_no: '',
            routing_no: '',
            territorycode: '',
            districtcode: '',
            usertype: '',
            created_by: '',
            updated_by: '',
        }),
      isAuthenticate: '',
      loader: false,
      isInsert: true
    };
  },
    created(){
        if (localStorage.getItem('auth') != null) {
            this.isAuthenticate = true;
            this.userId = this.$store.state.userid;
            const token = localStorage.getItem('auth');
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
            this.userList();
        }else{
            this.isAuthenticate = false;
            this.$router.push({name: 'Login'});
        }
    },
  mounted() {
      this.loader = true;
      $(this.$refs.modal).on('hidden.bs.modal', () => {
        this.form.reset();
    })

  },
  methods: {
    userList(url = this.base_url+'/api/admindashboard/users') {
        this.tableData.draw++;
      axios
        .get(url, { params: this.tableData }, { token: this.$store.state.token })
        .then((response) => {
            this.loader = false;
            if(this.tableData.draw == response.data.users.draw){
                this.usersList = response.data.users.data.data;
                this.configPagination(response.data.users.data);
            }

        })
        .catch((err) => {
            this.$toastr.error('Nothing Found');
        });
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
        this.userList();
    },
    getIndex(array, key, value){
        return array.findIndex(i => i[key] == value)
    },
    getServiceDetails() {
        axios.defaults.headers.common['Authorization'] = 'Bearer ' + this.$store.state.token;
        axios
        .get(this.base_url+"/api/service/servicedetails", { token: this.$store.state.token })
        .then((res) => {
          if (res.data.status == true) {
              this.districtList = res.data.district;
              this.territoryList = res.data.territory;
          }
        })
        .catch((err) => {
        });
    },
    createUser() {
        this.isInsert = true;
        $('.modal').modal();
        this.getServiceDetails();
    },
    saveUser() {
        if(this.isInsert){
        this.form.created_by = this.$store.state.userid;
        }else{
        this.form.updated_by = this.$store.state.userid;
        }
        this.form.post(this.base_url+'/api/auth/register')
        .then((response)=>{
                this.form.reset();
                $('.modal').modal('hide');
                this.$toastr.success('User created successfully');
                this.userList();

        }).catch((error)=>{
            this.$toastr.error('Something went wrong. Please try again');
        })
    },
    editUser(user) {
        this.isInsert = false;
        this.getServiceDetails();
        this.form.username = user.UserName;
        this.form.password = '123456';
        this.form.staffid = user.StaffID;
        this.form.mobile = user.MobileNo;
        this.form.address = user.Address;
        this.form.nid = user.NID;
        this.form.bank_name = user.bank_name;
        this.form.bank_acc_no = user.bank_acc_no;
        this.form.routing_no = user.routing_no;
        this.form.dob = user.DateOfBirth;
        this.form.territorycode = user.TerritoryCode;
        this.form.districtcode = user.DistrictCode;
        this.form.usertype = user.UserType;
        $('.modal').modal();
    },
    lockUser(staffId, unlock){
        this.form.staffid = staffId;
        if(unlock==true){
        this.form.active = 'Y';
        }else{
        this.form.active = 'N';
        }
        this.form.updated_by = this.$store.state.userid;
        this.form.post(this.base_url+'/api/auth/lockuser')
        .then((res) => {
          if (res.data.status == true) {
                if(unlock == true){
                    this.$toastr.success('User unlocked successfully');
                }else{
                    this.$toastr.success('User locked successfully');
                }
                this.userList();
          }
        })
        .catch((err) => {
            this.$toastr.error('Something went wrong!');
        });
    },
  }
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

/* ====== Toolbar Section ====== */
.toolbar-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  padding: 18px 20px;
  border-bottom: 1px solid #e9ecef;
  background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
}
.toolbar-left {
  display: flex;
  gap: 8px;
}
.btn-create {
  background: linear-gradient(135deg, #4a90d9 0%, #357abd 100%);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 9px 22px;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s;
  letter-spacing: 0.3px;
}
.btn-create:hover {
  background: linear-gradient(135deg, #357abd 0%, #2868a8 100%);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(53, 122, 189, 0.35);
  color: #fff;
}
.btn-create i {
  margin-right: 6px;
}
.toolbar-right {
  min-width: 280px;
}
.search-box {
  position: relative;
}
.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #adb5bd;
  font-size: 13px;
  z-index: 2;
}
.search-input {
  padding-left: 38px;
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
  padding: 11px 10px;
  border: none;
  white-space: nowrap;
  position: sticky;
  top: 0;
  z-index: 1;
}
.table-section >>> tbody td {
  padding: 10px 10px;
  font-size: 13px;
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
  color: #6c757d !important;
  font-size: 12px;
}
.td-staffid {
  font-weight: 700;
  color: #2c3e50 !important;
}
.td-name {
  font-weight: 600;
  color: #2c3e50 !important;
}
.td-action {
  white-space: nowrap;
}
.td-empty {
  text-align: center;
  padding: 50px 20px !important;
  color: #adb5bd;
}
.td-empty i {
  font-size: 36px;
  display: block;
  margin-bottom: 10px;
}
.td-empty p {
  margin: 0;
  font-size: 14px;
}

/* ====== Action Buttons ====== */
.action-group {
  display: inline-flex;
  gap: 5px;
}
.btn-action {
  width: 30px;
  height: 30px;
  padding: 0;
  border-radius: 6px;
  border: none;
  color: #fff;
  font-size: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.btn-lock {
  background: linear-gradient(135deg, #fdcb6e, #e17055);
}
.btn-lock:hover {
  transform: translateY(-2px);
  box-shadow: 0 3px 8px rgba(225, 112, 85, 0.35);
  color: #fff;
}
.btn-unlock {
  background: linear-gradient(135deg, #00b894, #55efc4);
}
.btn-unlock:hover {
  transform: translateY(-2px);
  box-shadow: 0 3px 8px rgba(0, 184, 148, 0.35);
  color: #fff;
}
.btn-edit {
  background: linear-gradient(135deg, #0984e3, #74b9ff);
}
.btn-edit:hover {
  transform: translateY(-2px);
  box-shadow: 0 3px 8px rgba(9, 132, 227, 0.35);
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
  min-width: 32px;
  height: 28px;
  padding: 0 8px;
  border-radius: 14px;
  font-size: 12px;
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

/* ====== User Type Badge ====== */
.badge-usertype {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.3px;
}
.badge-ut-a {
  background: linear-gradient(135deg, #6c5ce7, #a29bfe);
  color: #fff;
}
.badge-ut-u {
  background: #dfe6e9;
  color: #2d3436;
}
.badge-ut-cc {
  background: linear-gradient(135deg, #0984e3, #74b9ff);
  color: #fff;
}

/* ====== Status Badge ====== */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 12px;
  border-radius: 14px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.3px;
}
.status-active {
  background: linear-gradient(135deg, #00b894, #55efc4);
  color: #fff;
}
.status-active i {
  font-size: 10px;
}
.status-inactive {
  background: linear-gradient(135deg, #e17055, #fab1a0);
  color: #fff;
}
.status-inactive i {
  font-size: 10px;
}

/* ====== Footer ====== */
.table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 20px;
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
  padding: 16px 24px;
}
.modal-title-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}
.modal-title-wrap i {
  color: #74b9ff;
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
  padding: 24px;
  max-height: 65vh;
  overflow-y: auto;
}
.required-note {
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 8px 14px;
  font-size: 12px;
  color: #6c757d;
  margin-bottom: 20px;
}
.required-note i {
  color: #4a90d9;
  margin-right: 5px;
}
.required-field {
  color: #e74c3c;
  font-weight: 700;
}
.modern-form-group label {
  font-size: 12px;
  font-weight: 600;
  color: #495057;
  margin-bottom: 4px;
}
.modern-input {
  border-radius: 6px;
  border: 1.5px solid #dee2e6;
  font-size: 13px;
  padding: 8px 12px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.modern-input:focus {
  border-color: #4a90d9;
  box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.15);
}
.modern-input[readonly] {
  background: #f8f9fa;
  color: #6c757d;
}
.modern-modal-footer {
  border-top: 1px solid #e9ecef;
  padding: 14px 24px;
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
.btn-modal-save i, .btn-modal-cancel i {
  margin-right: 5px;
}
</style>

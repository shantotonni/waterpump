<template>
<div v-if="isAuthenticate">
<div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h3>Technician List</h3>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><router-link :to="{ name: 'Home' }">Home</router-link></li>
              <li class="breadcrumb-item active">Technician List</li>
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
        <p class="mt-2 text-muted">Loading technicians...</p>
      </div>

      <div class="modern-card" v-else>
        <!-- Toolbar Section -->
        <div class="toolbar-section">
          <div class="toolbar-left">
            <button class="btn btn-create" @click="createTechnician()">
              <i class="fas fa-plus"></i> Add Technician
            </button>
          </div>
          <div class="toolbar-right">
            <div class="search-box">
              <i class="fas fa-search search-icon"></i>
              <input class="form-control search-input" type="text" placeholder="Search technicians..." v-model="tableData.search" @input="getTechnicians()">
            </div>
          </div>
        </div>

        <!-- Table Section -->
        <div class="table-section">
          <data-table :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" @sort="sortBy">
            <tbody>
              <tr v-for="(tech, index) in technicianList" :key="tech.TechnicianID" :class="{'row-even': index % 2 === 0, 'row-odd': index % 2 !== 0}">
                <td class="td-action">
                  <div class="action-group">
                    <button class="btn btn-action btn-edit" @click.prevent="editTechnician(tech)" title="Edit">
                      <i class="fas fa-pen"></i>
                    </button>
                    <button class="btn btn-action btn-delete" @click.prevent="deleteTechnician(tech.TechnicianID)" title="Delete">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </td>
                <td class="td-sl">{{ pagination.from + index }}</td>
                <td class="td-name">{{ tech.Name }}</td>
                <td>{{ tech.MobileNo }}</td>
                <td>{{ tech.Address }}</td>
                <td class="td-territory">
                  <span class="territory-badge">{{ tech.Territories }}</span>
                </td>
                <td>{{ tech.BankAccountNo }}</td>
                <td>{{ tech.RoutingNo }}</td>
                <td>{{ tech.BankName }}</td>
              </tr>
              <tr v-if="technicianList.length === 0">
                <td colspan="9" class="td-empty">
                  <i class="fas fa-hard-hat"></i>
                  <p>No technicians found</p>
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
                    @prev="getTechnicians(pagination.prevPageUrl)"
                    @next="getTechnicians(pagination.nextPageUrl)">
            </pagination>
          </div>
        </div>
      </div>

      <!-- MODAL -->
      <div ref="modal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content modern-modal">
            <div class="modal-header modern-modal-header">
              <div class="modal-title-wrap">
                <i class="fas" :class="isInsert ? 'fa-user-plus' : 'fa-user-edit'"></i>
                <h5 class="modal-title" v-if="isInsert">Add Technician</h5>
                <h5 class="modal-title" v-else>Edit Technician</h5>
              </div>
              <button type="button" class="close modern-close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form @submit.prevent="saveTechnician">
              <div class="modal-body modern-modal-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group modern-form-group">
                      <label><i class="fas fa-user"></i> Name</label>
                      <input type="text" v-model="form.name" class="form-control modern-input" placeholder="Enter technician name">
                    </div>
                    <div class="form-group modern-form-group">
                      <label><i class="fas fa-phone"></i> Mobile No</label>
                      <input type="text" v-model="form.mobile" class="form-control modern-input" placeholder="Enter mobile number">
                    </div>
                    <div class="form-group modern-form-group">
                      <label><i class="fas fa-map-marker-alt"></i> Address</label>
                      <input type="text" v-model="form.address" class="form-control modern-input" placeholder="Enter address">
                    </div>
                    <div class="form-group modern-form-group">
                      <label><i class="fas fa-credit-card"></i> Bank Account No</label>
                      <input type="text" v-model="form.bank_account" class="form-control modern-input" placeholder="Enter account number">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group modern-form-group">
                      <label><i class="fas fa-route"></i> Routing No</label>
                      <input type="text" v-model="form.routing_no" class="form-control modern-input" placeholder="Enter routing number">
                    </div>
                    <div class="form-group modern-form-group">
                      <label><i class="fas fa-university"></i> Bank Name</label>
                      <input type="text" v-model="form.bank_name" class="form-control modern-input" placeholder="Enter bank name">
                    </div>
                    <div class="form-group modern-form-group">
                      <label><i class="fas fa-map"></i> Territories</label>
                      <div class="territory-selector" :class="{'disabled': form.is_national}">
                        <div class="territory-selected-box" @click="toggleTerritoryDropdown" v-if="!form.is_national">
                          <div class="territory-tags" v-if="form.territories.length">
                            <span class="territory-tag" v-for="(t, idx) in form.territories" :key="t.TTYCode">
                              {{ t.TTYName }}
                              <i class="fas fa-times territory-tag-remove" @click.stop="removeTerritory(idx)"></i>
                            </span>
                          </div>
                          <span class="territory-placeholder" v-else>Select Territories</span>
                          <i class="fas fa-chevron-down territory-arrow" :class="{'rotate': showTerritoryDropdown}"></i>
                        </div>
                        <div class="territory-selected-box" v-else>
                          <span class="territory-all-label"><i class="fas fa-globe-asia"></i> All Territories (National)</span>
                        </div>
                        <div class="territory-dropdown" v-if="showTerritoryDropdown && !form.is_national">
                          <input type="text" class="territory-search" v-model="territorySearch" placeholder="Search territory...">
                          <div class="territory-options">
                            <label class="territory-option" v-for="t in filteredTerritories" :key="t.TTYCode"
                                :class="{'selected': isTerritorySelected(t)}">
                              <input type="checkbox" :checked="isTerritorySelected(t)" @change="toggleTerritory(t)">
                              <span>{{ t.TTYName }}</span>
                            </label>
                            <div class="territory-no-result" v-if="filteredTerritories.length === 0">
                              No territories found
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group modern-form-group">
                      <label class="national-check">
                        <input type="checkbox" v-model="form.is_national" @change="onNationalChange" />
                        <span><i class="fas fa-globe-asia"></i> National Technician (All Territories)</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer modern-modal-footer">
                <button type="button" class="btn btn-modal-cancel" data-dismiss="modal">
                  <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-modal-save">
                  <i class="fas" :class="isInsert ? 'fa-save' : 'fa-check'"></i> {{ isInsert ? 'Save Technician' : 'Save Changes' }}
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
            {label:'Action', name:'action'},
            {label:'SL', name:'id'},
            {label:'Technician Name', name:'name'},
            {label:'Mobile No', name:'mobile'},
            {label:'Address', name:'address'},
            {label:'Territories', name:'territories'},
            {label:'Bank Account No', name:'bank_account'},
            {label:'Routing No', name:'routing_no'},
            {label:'Bank Name', name:'bank_name'},
        ];
        columns.forEach((column) => {
            sortOrders[column.name] = 1;
        });
        return {
            base_url: '/waterpump/public',
            technicianList: [],
            columns,
            sortKey: 'name',
            sortOrders,
            perPage: ['10','20','30'],
            tableData: { draw:0, length:10, search:'', column:0, dir:'desc' },
            // pagination:{},
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

            loader: false,
            isInsert: true,
            territoryList: [],
            showTerritoryDropdown: false,
            territorySearch: '',
            form: {
                id: null,
                name: '',
                mobile: '',
                address: '',
                bank_account: '',
                routing_no: '',
                bank_name: '',
                territories: [],
                is_national: false
            },
            isAuthenticate: ''
        };
    },
    computed: {
        filteredTerritories() {
            if (!this.territorySearch) return this.territoryList;
            const s = this.territorySearch.toLowerCase();
            return this.territoryList.filter(t => t.TTYName.toLowerCase().includes(s));
        }
    },
    created(){
        if(localStorage.getItem('auth') != null){
            this.isAuthenticate = true;
            axios.defaults.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('auth');
        } else {
            this.isAuthenticate = false;
            this.$router.push({name:'Login'});
        }
    },
    mounted() {
        this.loader = true;
        $(this.$refs.modal).on('hidden.bs.modal', () => {
            this.resetForm();
        })
        this.getTechnicians();
        this.getTerritories();
        document.addEventListener('click', this.closeTerritoryDropdown);
    },
    beforeDestroy() {
        document.removeEventListener('click', this.closeTerritoryDropdown);
    },
    methods: {
        getTerritories() {
            return axios.get(this.base_url + '/api/service/getallterritories').then((res) => {
                this.territoryList = res.data.data || [];
            }).catch((error) => {
                console.error('Territory load error:', error);
                this.$toastr.error('Failed to load territories');
            });
        },

        getTechnicians(url = this.base_url + '/api/service/alltechnicians'){
            this.loader = true;
            axios.get(url, { token: this.$store.state.token, params: this.tableData })
            .then(res=>{
                this.loader = false;
                this.technicianList = res.data.data;
                this.configPagination(res.data);
            })
            .catch(()=> this.$toastr.error('Error loading data'));
        },

        configPagination(data){
            this.pagination = {
                lastPage:data.last_page,
                currentPage:data.current_page,
                total:data.total,
                nextPageUrl:data.next_page_url,
                prevPageUrl:data.prev_page_url,
                from: data.from,
                to: data.to
            };
        },

        sortBy(key){
            this.sortKey = key;
            this.sortOrders[key] *= -1;
            this.getTechnicians();
        },
        
        getIndex(array, key, value){
            return array.findIndex(i => i[key] == value)
        },

        createTechnician(){
            this.isInsert = true;
            this.resetForm();
            $('.modal').modal();
        },

        saveTechnician(){
            const url = this.isInsert 
                ? this.base_url + '/api/service/savelocaltechnician'
                : this.base_url + '/api/service/updatelocaltechnician/' + this.form.id;

            axios.post(url, this.form)
            .then((res) => {
                if (res.data.status === true) {
                    this.$toastr.success(res.data.message || 'Saved successfully');
                    $(this.$refs.modal).modal('hide');
                    this.getTechnicians();
                } 
                else {
                    this.$toastr.error(res.data.message || 'Something went wrong');
                }
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    // Loop through validation errors and show all
                    Object.values(errors).forEach(errArray => {
                        this.$toastr.error(errArray[0]);
                    });
                } else if (error.response && error.response.data.message) {
                    this.$toastr.error(error.response.data.message);
                } else {
                    this.$toastr.error('Failed to save technician');
                }
            });
        },

        editTechnician(tech){
            this.isInsert = false;

            if (!this.territoryList.length) {
                this.getTerritories().then(() => this.editTechnician(tech));
                return;
            }

            const isNational = tech.TerritoriesArray.includes('%');

            const selectedTerritories = isNational
                ? [] // no need to select any if national
                : this.territoryList.filter(t =>
                    tech.TerritoriesArray.includes(t.TTYCode)
                );

            this.form = {
                id: tech.TechnicianID,
                name: tech.Name,
                mobile: tech.MobileNo,
                address: tech.Address,
                bank_account: tech.BankAccountNo,
                routing_no: tech.RoutingNo,
                bank_name: tech.BankName,
                territories: selectedTerritories,
                is_national: isNational
            };
            $('.modal').modal();
        },

        deleteTechnician(id){
            if(confirm('Are you sure?')){
                axios.delete(this.base_url + '/api/service/deletelocaltechnician/' + id)

                .then((res) => {
                    if (res.data.status === true) {
                        this.$toastr.success(res.data.message || 'Deleted successfully');
                        this.getTechnicians();
                    } 
                    else {
                        this.$toastr.error(res.data.message || 'Something went wrong');
                    }
                })
                .catch((error) => {
                    if (error.response && error.response.status === 422) {
                        const errors = error.response.data.errors;
                        // Loop through validation errors and show all
                        Object.values(errors).forEach(errArray => {
                            this.$toastr.error(errArray[0]);
                        });
                    } else if (error.response && error.response.data.message) {
                        this.$toastr.error(error.response.data.message);
                    } else {
                        this.$toastr.error('Failed to save technician');
                    }
                });
            }
        },

        resetForm(){
            this.form = {
                id:null, name:'', mobile:'', address:'',
                bank_account:'', routing_no:'', bank_name:'', territories: [],
                is_national: false
            };
            this.showTerritoryDropdown = false;
            this.territorySearch = '';
        },

        toggleTerritoryDropdown() {
            this.showTerritoryDropdown = !this.showTerritoryDropdown;
            this.territorySearch = '';
        },

        isTerritorySelected(territory) {
            return this.form.territories.some(t => t.TTYCode === territory.TTYCode);
        },

        toggleTerritory(territory) {
            const idx = this.form.territories.findIndex(t => t.TTYCode === territory.TTYCode);
            if (idx >= 0) {
                this.form.territories.splice(idx, 1);
            } else {
                this.form.territories.push(territory);
            }
        },

        removeTerritory(index) {
            this.form.territories.splice(index, 1);
        },

        onNationalChange() {
            if (this.form.is_national) {
                this.form.territories = [];
                this.showTerritoryDropdown = false;
            }
        },

        closeTerritoryDropdown(e) {
            if (this.$refs.modal && !e.target.closest('.territory-selector')) {
                this.showTerritoryDropdown = false;
            }
        },
    }
};
</script>

<style>
/* Unscoped: ensure territory dropdown is visible inside Bootstrap modal */
.bd-example-modal-lg .modal-body {
    overflow: visible !important;
}
.bd-example-modal-lg .modal-content {
    overflow: visible !important;
}
</style>
<style scoped>
/* Loader */
.modern-loader {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 20px;
}
.modern-loader .spinner-border {
    width: 3rem;
    height: 3rem;
}

/* Card Container */
.modern-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    overflow: hidden;
}

/* Toolbar */
.toolbar-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #e9ecef;
    flex-wrap: wrap;
    gap: 10px;
}
.btn-create {
    background: linear-gradient(135deg, #4a90d9, #357abd);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s;
}
.btn-create:hover {
    background: linear-gradient(135deg, #357abd, #2a6aad);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(74,144,217,0.35);
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
}
.search-input {
    padding-left: 36px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    font-size: 13px;
    height: 38px;
    min-width: 220px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.search-input:focus {
    border-color: #4a90d9;
    box-shadow: 0 0 0 3px rgba(74,144,217,0.15);
}

/* Table */
.table-section {
    overflow-x: auto;
}
.table-section >>> .tableFixHead {
    height: auto;
    max-height: 520px;
}
.table-section >>> table {
    font-size: 12px;
    margin-bottom: 0;
}
.table-section >>> thead th {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: #fff;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 10px 12px;
    border: none;
    white-space: nowrap;
}
.table-section >>> thead th:hover {
    background: linear-gradient(135deg, #34495e, #3d566e);
}
.table-section >>> tbody td {
    padding: 8px 12px;
    vertical-align: middle;
    border-color: #f0f0f0;
    font-size: 12px;
}
.row-even {
    background-color: #fff;
}
.row-odd {
    background-color: #f8f9fc;
}
.table-section >>> tbody tr:hover {
    background-color: #edf2fb !important;
}

/* Action Buttons */
.action-group {
    display: flex;
    gap: 6px;
    justify-content: center;
}
.btn-action {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    color: #fff;
    font-size: 12px;
    transition: all 0.2s;
    padding: 0;
}
.btn-action:hover {
    transform: scale(1.15);
}
.btn-edit {
    background: linear-gradient(135deg, #4a90d9, #357abd);
}
.btn-edit:hover {
    box-shadow: 0 3px 10px rgba(74,144,217,0.4);
    color: #fff;
}
.btn-delete {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
}
.btn-delete:hover {
    box-shadow: 0 3px 10px rgba(231,76,60,0.4);
    color: #fff;
}

/* Table Cell Styles */
.td-action {
    width: 90px;
}
.td-sl {
    width: 50px;
    font-weight: 600;
    color: #6c757d;
}
.td-name {
    font-weight: 600;
    color: #2c3e50;
}
.td-territory {
    max-width: 200px;
}
.territory-badge {
    display: inline-block;
    background: linear-gradient(135deg, #a29bfe, #6c5ce7);
    color: #fff;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Empty State */
.td-empty {
    padding: 40px 20px !important;
    text-align: center;
    color: #adb5bd;
}
.td-empty i {
    font-size: 36px;
    margin-bottom: 10px;
    display: block;
}
.td-empty p {
    margin: 0;
    font-size: 14px;
}

/* Footer */
.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    border-top: 1px solid #e9ecef;
    flex-wrap: wrap;
    gap: 10px;
}
.footer-info {
    font-size: 13px;
}

/* Modal */
.modern-modal {
    border: none;
    border-radius: 12px;
    overflow: visible;
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}
.modern-modal-header {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: #fff;
    border: none;
    padding: 16px 20px;
    border-radius: 12px 12px 0 0;
}
.modal-title-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}
.modal-title-wrap i {
    font-size: 18px;
    opacity: 0.9;
}
.modern-modal-header .modal-title {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}
.modern-close {
    color: #fff;
    opacity: 0.8;
    text-shadow: none;
    font-size: 24px;
}
.modern-close:hover {
    opacity: 1;
    color: #fff;
}

/* Modal Body */
.modern-modal-body {
    padding: 24px 20px;
    overflow: visible;
}
.modern-form-group {
    margin-bottom: 16px;
}
.modern-form-group label {
    font-size: 13px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.modern-form-group label i {
    color: #4a90d9;
    font-size: 13px;
}
.modern-input {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 9px 14px;
    font-size: 13px;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.modern-input:focus {
    border-color: #4a90d9;
    box-shadow: 0 0 0 3px rgba(74,144,217,0.15);
}
.national-check {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 13px;
    margin-top: 8px;
}
.national-check input[type="checkbox"] {
    width: 16px;
    height: 16px;
    accent-color: #4a90d9;
}
.national-check span {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #2c3e50;
    font-weight: 500;
}

/* Modal Footer */
.modern-modal-footer {
    border-top: 1px solid #e9ecef;
    padding: 14px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}
.btn-modal-cancel {
    background: #f1f3f5;
    color: #495057;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s;
}
.btn-modal-cancel:hover {
    background: #e9ecef;
}
.btn-modal-save {
    background: linear-gradient(135deg, #4a90d9, #357abd);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 8px 22px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s;
}
.btn-modal-save:hover {
    background: linear-gradient(135deg, #357abd, #2a6aad);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(74,144,217,0.35);
    color: #fff;
}

/* Territory Selector */
.territory-selector {
    position: relative;
}
.territory-selector.disabled {
    opacity: 0.7;
}
.territory-selected-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 40px;
    padding: 6px 12px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    cursor: pointer;
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
    flex-wrap: wrap;
    gap: 4px;
}
.territory-selected-box:hover {
    border-color: #4a90d9;
}
.territory-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    flex: 1;
}
.territory-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: linear-gradient(135deg, #4a90d9, #357abd);
    color: #fff;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
}
.territory-tag-remove {
    cursor: pointer;
    font-size: 10px;
    opacity: 0.8;
    margin-left: 2px;
}
.territory-tag-remove:hover {
    opacity: 1;
}
.territory-placeholder {
    color: #adb5bd;
    font-size: 13px;
}
.territory-all-label {
    color: #6c5ce7;
    font-size: 13px;
    font-weight: 500;
}
.territory-all-label i {
    margin-right: 4px;
    color: #6c5ce7 !important;
}
.territory-arrow {
    font-size: 11px;
    color: #adb5bd;
    transition: transform 0.2s;
    margin-left: 8px;
    flex-shrink: 0;
}
.territory-arrow.rotate {
    transform: rotate(180deg);
}
.territory-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 9999;
    background: #fff;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    max-height: 220px;
    display: flex;
    flex-direction: column;
}
.territory-search {
    width: 100%;
    border: none;
    border-bottom: 1px solid #e9ecef;
    padding: 8px 12px;
    font-size: 12px;
    outline: none;
}
.territory-search:focus {
    background: #f8f9fa;
}
.territory-options {
    overflow-y: auto;
    max-height: 180px;
}
.territory-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 400 !important;
    color: #495057 !important;
    margin-bottom: 0 !important;
    transition: background 0.15s;
}
.territory-option:hover {
    background: #f0f4ff;
}
.territory-option.selected {
    background: #e8f0fe;
    font-weight: 500 !important;
    color: #2c3e50 !important;
}
.territory-option input[type="checkbox"] {
    width: 15px;
    height: 15px;
    accent-color: #4a90d9;
    flex-shrink: 0;
}
.territory-no-result {
    padding: 12px;
    text-align: center;
    color: #adb5bd;
    font-size: 12px;
}
</style>

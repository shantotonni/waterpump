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
        <div class="text-center" v-if="loader">
            <div class="spinner-border" role="status">
                <span class="sr-only"><p style="color:red">Loading...</p></span>
            </div>
        </div>
        <div class="card" v-else>
            <div class="card-body">
                <div>
                    <div class="input-group" style="padding-left: 0px;">
                        <div class="col-md-8">
                            <div class="input-group-prepend">
                                <button class="btn btn-primary mb-3" @click="createTechnician()">Add Technician</button>
                            </div>
                        </div>
                        <div class="col-md-4 input-group mb-3" style="padding-right: 0px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Search</span>
                            </div>
                            <input class="form-control" type="text" v-model="tableData.search" @input="getTechnicians()">
                        </div>
                    </div>
                    <data-table :columns="columns" :sortKey="sortKey" :sortOrders="sortOrders" @sort="sortBy">
                        <tbody>
                            <tr v-for="(tech, index) in technicianList" :key="tech.TechnicianID">
                                <td>
                                    <a @click.prevent="editTechnician(tech)" style="cursor:pointer;color:#3490dc;">Edit</a>
                                    |
                                    <a @click.prevent="deleteTechnician(tech.TechnicianID)" style="cursor:pointer;color:red;">Delete</a>
                                </td>
                                <td>{{ pagination.from + index }}</td>
                                <td>{{ tech.Name }}</td>
                                <td>{{ tech.MobileNo }}</td>
                                <td>{{ tech.Address }}</td>
                                <td>{{ tech.Territories }}</td>
                                <td>{{ tech.BankAccountNo }}</td>
                                <td>{{ tech.RoutingNo }}</td>
                                <td>{{ tech.BankName }}</td>
                            </tr>
                        </tbody>
                    </data-table>
                </div>

                <div class="col-md-12 input-group">
                    <div class="col-md-6 invisible">
                        <div class="col-md-4 input-group-prepend justify-content-center">
                            <span class="mt-2">Show:</span>&nbsp;
                            <select class="form-control" v-model="tableData.length" @change="getTechnicians()">
                                <option v-for="(records, index) in perPage" :key="index" :value="records">{{records}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex flex-row-reverse">
                        <pagination :pagination="pagination"
                                @prev="getTechnicians(pagination.prevPageUrl)"
                                @next="getTechnicians(pagination.nextPageUrl)">
                        </pagination>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <div ref="modal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" v-if="isInsert">Add Technician</h5>
                    <h5 class="modal-title" v-else>Edit Technician</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form @submit.prevent="saveTechnician">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" v-model="form.name" class="form-control" placeholder="Enter technician name">
                                </div>
                                <div class="form-group">
                                    <label>Mobile No</label>
                                    <input type="text" v-model="form.mobile" class="form-control" placeholder="Enter mobile number">
                                </div>
                                <div class="form-group">
                                    <label>Address</label>
                                    <input type="text" v-model="form.address" class="form-control" placeholder="Enter address">
                                </div>
                                <div class="form-group">
                                    <label>Bank Account No</label>
                                    <input type="text" v-model="form.bank_account" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Routing No</label>
                                    <input type="text" v-model="form.routing_no" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Bank Name</label>
                                    <input type="text" v-model="form.bank_name" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Territories</label>
                                    <multiselect
                                        v-model="form.territories"
                                        :options="territoryList"
                                        label="TTYName"
                                        track-by="TTYCode"
                                        :multiple="true"
                                        :close-on-select="true"
                                        placeholder="Select Territories"
                                        :disabled="form.is_national"
                                    ></multiselect>
                                </div>
                                <div class="form-group">
                                    <label><input type="checkbox" v-model="form.is_national" /> National Technician (All Territories)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">{{ isInsert ? 'Save' : 'Update' }}</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
import Multiselect from "vue-multiselect";

    export default {
        components: {
            'data-table': DataTable,
            'pagination': Pagination,
            Multiselect 
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
            // this.form.reset();
            this.resetForm();
        })
        this.getTechnicians();
        this.getTerritories();
    },
    methods: {
        getTerritories() {
            axios.get(this.base_url + '/api/service/getallterritories', { token: this.$store.state.token }).then((res) => {
                this.territoryList = res.data.data;
            }).catch(() => {
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
                bank_account:'', routing_no:'', bank_name:'', territories: []
            };
        },
    }
};
</script>

<style src="vue-multiselect/dist/vue-multiselect.min.css"></style>
<style scoped>
.activestatus{
    color: green;
    font-weight: 600;
}
.inactivestatus{
    color: red;
    font-weight: 600;
}
.required-field{
    color: red;
}
</style>

<template>
  <div v-if="this.$store.state.usertype!='CC'">
    <div v-if="isAuthenticate">
      <div class="content-wrapper">
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="#">Home</a></li>
                  <li class="breadcrumb-item active">Dashboard</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
        <section class="content">
          <div class="container-fluid">

            <div class="row">
              <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-info">
                  <span class="info-box-icon"><i class="fas fa-users-cog"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text">Total Service</span>
                    <span class="info-box-number">{{ totalService }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-success">
                  <span class="info-box-icon"><i class="far fa-calendar-alt"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text">Current Month</span>
                    <span class="info-box-number">{{ monthlyService }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-warning">
                  <span class="info-box-icon"><i class="far fa-clock"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text">Current Day</span>
                    <span class="info-box-number">{{ dailyService }}</span>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-primary">
                  <span class="info-box-icon"><i class="far fa-star"></i></span>

                  <div class="info-box-content">
                    <span class="info-box-text">Average CSI Rating</span>
                    <span class="info-box-number">{{ avgCSIRating }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 col-sm-12 col-12">
                <div class="col-md-12 text-center mb-3 bold">
                  <span><b>Territory Wise
                    Today's Service Provided - {{ moment().format('DD-MM-YYYY') }}
                  </b>
                  </span>
                  <p>Total Services: {{serviceCount}}</p>
                </div>
                <div class="col-md-12">
                  <form class="form-inline">
                    <div class="form-group">
                      <p class="form-control-static">From Date</p>
                    </div>
                    <div class="form-group mx-sm-3">
                      <input class="form-control" type="date" v-model="formData.fromDate">
                    </div>
                    <div class="form-group">
                      <p class="form-control-static">To Date</p>
                    </div>
                    <div class="form-group mx-sm-3">
                      <input class="form-control" type="date" v-model="formData.toDate">
                    </div>
                    <div class="form-group">
                      <button type="submit" @click.prevent="ttyWiseDailyServiceChart()"
                              class="btn btn-default button-border"><i class="fas fa-filter"></i> Filter
                      </button>
                    </div>
                    <div class="form-group mx-sm-3">
                      <button type="submit" @click.prevent="clearFilter()" class="btn btn-default button-border">Clear
                        Filter
                      </button>
                    </div>
                  </form>
                </div>
              </div>
              <div class="col-md-12 col-sm-12 col-12 mb-3">
                <div id="chart">
                  <apexchart ref="realtimeChart" type="bar" height="380" :options="chartOptions"
                             :series="series"></apexchart>
                </div>
              </div>

              <div class="col-md-12 col-sm-12 col-12 mb-3 mt-3">
                <div class="col-md-12 text-center mb-3 bold">
                  <span><b>Territory Wise monthly Service Ratio - {{ currentMonth() }}</b></span>
                </div>
                <div class="col-md-12">
                  <form class="form-inline">
                    <div class="form-group">
                      <p class="form-control-static">Month</p>
                    </div>
                    <div class="form-group mx-sm-3">
                      <input class="form-control" type="month" v-model="formData.fromDate">
                    </div>
                    <div class="form-group">
                      <button type="submit" @click.prevent="ttyWiseMonthlyServiceRatio()"
                              class="btn btn-default button-border"><i class="fas fa-filter"></i> Filter
                      </button>
                    </div>
                    <div class="form-group mx-sm-3">
                      <button type="submit" @click.prevent="clearPiFilter()" class="btn btn-default button-border">Clear
                        Filter
                      </button>
                    </div>
                  </form>
                </div>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-3"></div>
              <div class="col-lg-6 col-md-6 col-sm-6">
                <br><br>
                <div id="chart">
                  <apexchart type="pie" height="250" :options="piChartOptions" :series="piSeries"></apexchart>
                </div>
              </div>
              <div class="col-lg-6 col-md-3 col-sm-3"></div>
              <div class="col-md-12 col-sm-12 col-12 mb-3 mt-3"></div>

              <!-- <div class="col-md-12 col-sm-12 col-12">
                  <div class="col-md-12 text-center mb-3 bold">
                      <span><b>SPLY Wise Service Comparison</b></span>
                  </div>
                  <div class="col-md-12">
                      <form class="form-inline">
                          <div class="form-group">
                              <p class="form-control-static">Year 1</p>
                          </div>
                          <div class="form-group mx-sm-3">
                              <input class="form-control" type="year" v-model="formData.fromDate">
                          </div>
                          <div class="form-group">
                              <p class="form-control-static">Year 2</p>
                          </div>
                          <div class="form-group mx-sm-3">
                              <input class="form-control" type="year" v-model="formData.toDate">
                          </div>
                          <div class="form-group">
                              <button type="submit" @click.prevent="ttyWiseDailyServiceChart()" class="btn btn-default button-border"><i class="fas fa-filter"></i> Filter</button>
                          </div>
                          <div class="form-group mx-sm-3">
                          <button type="submit" @click.prevent="clearFilter()" class="btn btn-default button-border">Clear Filter</button>
                          </div>
                      </form>
                  </div>
              </div>
              <div class="col-md-12 col-sm-12 col-12 mb-3">
                  <div id="chart">
                      <apexchart type="bar" height="350" :options="splyChartOptions" :series="splySeries"></apexchart>
                  </div>
              </div> -->

            </div>
            <div class="row">
              <div class="col-md-12 col-sm-12 col-12">
                <div class="col-md-12 text-center mb-3 bold">
                  <span><b>Compare Services Between Years</b></span>
                </div>
                <div class="col-md-12">
                  <form class="form-inline">
                    <div class="form-group">
                      <p class="form-control-static">Year</p>
                    </div>
                    <div class="form-group mx-sm-3">
                      <input class="form-control" step="1" type="number" v-model="formData.fromYear">
                    </div>
                    <div class="form-group">
                      <p class="form-control-static">Compare Year</p>
                    </div>
                    <div class="form-group mx-sm-3">
                      <input class="form-control" type="number" v-model="formData.toYear">
                    </div>
                    <div class="form-group">
                      <button type="submit" @click.prevent="yearWiseCompare()"
                              class="btn btn-default button-border"><i class="fas fa-filter"></i> Filter
                      </button>
                    </div>
                    <div class="form-group mx-sm-3">
                      <button type="submit" @click.prevent="clearBarFilter()" class="btn btn-default button-border">Clear
                        Filter
                      </button>
                    </div>
                  </form>
                </div>
              </div>
              <div class="col-md-12 col-sm-12 col-12 mb-3">
                <div id="basic-bar">
                  <apexchart type="bar" height="380" :options="barOptions"
                             :series="seriesBar"></apexchart>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
    <div v-else>Please Login</div>
  </div>
  <div v-else>
    <customer-care></customer-care>
  </div>
</template>

<script>
import axios from "axios";
import CustomerCareDashboard from '../components/customercare/CustomerCareDashboard';
import VueApexCharts from 'vue-apexcharts';
import moment from 'moment'

export default {
  name: "Home",
  data: () => ({
    return: {
      responseData: []
    },
    series: [{
      name: 'No. of Service(s)',
      data: []
    }],
    seriesBar: [],
    piSeries: [],
    splySeries: [{
      name: 'Revenue',
      data: [76, 85, 101, 98, 87, 105, 91, 114, 94]
    }, {
      name: 'Free Cash Flow',
      data: [35, 41, 36, 26, 45, 48, 52, 53, 41]
    }],
    chartOptions: {
      chart: {
        type: 'bar',
        height: 600,
        width: '100%',
        toolbar: {
          show: true,
          tools: {
            download: true
          }
        }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '80%'
        },
      },
      dataLabels: {
        enabled: true
      },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
      },
      xaxis: {
        title: {
          text: 'Territory'
        },
        categories: [],
      },
      yaxis: {
        type: 'numeric',
        show: true,
        showAlways: true,
        opposite: false,
        reversed: false,
        logarithmic: false,
        //tickAmount: 6,
        //min: 0,
        //max: 200,
        floating: false,
        axisBorder: {
          show: true,
          color: '#78909C',
          offsetX: 0,
          offsetY: 0
        },
        axisTicks: {
          show: true,
          borderType: 'solid',
          color: '#78909C',
          width: 6,
          offsetX: 0,
          offsetY: 0
        },
        title: {
          text: 'No. of Service (s)'
        },
        labels: {
          formatter: function (val, index) {
            return val.toFixed();
          }
        }
      },
      fill: {
        opacity: 1
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return val
          }
        }
      },
      noData: {
        text: 'No Data Found',
        align: 'center',
        verticalAlign: 'top',
        offsetX: 0,
        offsetY: 0,
        style: {
          color: '#78909C',
          fontSize: '14px',
        }
      }
    },
    barOptions: {
      chart: {
        id: 'basic-bar'
      },
      xaxis: {
        categories: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August','September','October','November','December']
      }
    },
    piChartOptions: {
      chart: {
        type: 'pie',
        toolbar: {
          show: true,
          tools: {
            download: true
          }
        }
      },
      labels: [],
      theme: {
        monochrome: {
          enabled: false
        }
      },
      plotOptions: {
        pie: {
          offsetX: 0,
          offsetY: 40,
          startAngle: 0,
          endAngle: 360,
          expandOnClick: true,
          dataLabels: {
            offset: 60,
            minAngleToShowLabel: 10
          }
        }
      },
      dataLabels: {
        formatter(val, opts) {
          const name = opts.w.globals.labels[opts.seriesIndex]
          return [name, val.toFixed(1) + '%']
        },
        offsetX: 0,
        offsetY: 0,
        style: {
          fontSize: '10px',
          fontFamily: 'Helvetica, Arial, sans-serif',
          fontWeight: 'normal',
          colors: ['#000']
        },
        dropShadow: {
          enabled: true,
          top: 0,
          left: 0,
          blur: 0,
          color: '#000',
          opacity: 1
        }
      },
      legend: {
        show: false
      }
    },
    splyChartOptions: {
      chart: {
        type: 'bar',
        height: 350
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '55%',
          endingShape: 'rounded'
        },
      },
      dataLabels: {
        enabled: false
      },
      stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
      },
      xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
      },
      yaxis: {
        show: true,
        showAlways: true,
        opposite: false,
        reversed: false,
        logarithmic: false,
        tickAmount: 4,
        min: 0,
        max: 1000,
        floating: false,
        axisBorder: {
          show: true,
          color: '#78909C',
          offsetX: 0,
          offsetY: 0
        },
        axisTicks: {
          show: true,
          borderType: 'solid',
          color: '#78909C',
          width: 6,
          offsetX: 0,
          offsetY: 0
        },
        title: {
          text: 'No. of Service (s)'
        }
      },
      fill: {
        opacity: 1
      },
      tooltip: {
        y: {
          formatter: function (val) {
            return val
          }
        }
      }
    },
    isAuthenticate: '',
    totalService: '',
    monthlyService: '',
    dailyService: '',
    avgCSIRating: '',
    loader: false,
    formData: {
      fromDate: moment().startOf('month').format('YYYY-MM-DD'),
      toDate: moment().endOf('month').format('YYYY-MM-DD'),
      fromYear: moment().format('YYYY') - 1,
      toYear: moment().format('YYYY')
    },
    base_url: '/waterpump/public',
    serviceCount: 0
  }),
  mounted() {
    // console.log(this.chartOptions);
    if (localStorage.getItem('auth') != null) {
      this.isAuthenticate = true;
      const token = localStorage.getItem('auth')
      axios.defaults.headers.common['Authorization'] = 'Bearer ' + token
      this.loader = true;
      this.dashboard();
      this.ttyWiseDailyServiceChart();
      this.ttyWiseMonthlyServiceRatio();
      this.splyWiseServiceComparison();
      this.yearWiseCompare();
    } else {
      this.$router.push({name: 'Login'});
    }
  },
  methods: {
    dashboard() {
      axios.get(this.base_url + "/api/admindashboard/dashboardSummary", {token: this.$store.state.token}).then((res) => {
        console.log(res)
        if (res) {
          this.totalService = res.data.TotalServiceGiven;
          this.monthlyService = res.data.CurrentMonthServiceGiven;
          this.dailyService = res.data.CurrentDayServiceGiven;
          this.avgCSIRating = res.data.AvgCSIRating;
        }
      })
          .catch((err) => {
            console.log(err);
          });
    },
    ttyWiseDailyServiceChart() {
      axios.get(this.base_url + "/api/admindashboard/ttywisedailyservicechart", {params: this.formData}, {token: this.$store.state.token}).then((res) => {
        if (res) {
          this.avgCSIRating = res.data.AvgCSIRating;
          this.chartOptions.xaxis.categories.splice(0);
          this.series[0].data.splice(0);
          var result = res.data.TTyWiseTodayServiceProvided;
          // console.log(res);
          let sum = 0
          for (var i = 0; i < result.length; i++) {
            this.chartOptions.xaxis.categories.push(result[i].TTYName);
            this.series[0].data.push(parseInt(result[i].NoOfServices));
            sum  += Number(result[i].NoOfServices)
          }
          this.serviceCount = sum
          this.$refs.realtimeChart.updateSeries([{
            data: this.series[0].data,
          }], false, true);
          // console.log(this.series[0]);
          // console.log(this.chartOptions.xaxis);
        }
      })
          .catch((err) => {
            console.log(err);
          });
    },
    ttyWiseMonthlyServiceRatio() {
      axios
          .get(this.base_url + "/api/admindashboard/ttywisemonthlyserviceratio", {params: this.formData}, {token: this.$store.state.token})
          .then((res) => {
            if (res) {
              // console.log(res.data.TTyWiseMonthlyServiceRatio);
              this.piChartOptions.labels.splice(0);
              this.piSeries.splice(0);
              var result = res.data.TTyWiseMonthlyServiceRatio;
              for (var i = 0; i < result.length; i++) {
                this.piChartOptions.labels.push(result[i].TTYName);
                this.piSeries.push(parseInt(result[i].NoOfServices))
              }
              // console.log(this.piSeries);
              // console.log(this.piChartOptions);
            }
          })
          .catch((err) => {
            console.log(err);
          });
    },
    splyWiseServiceComparison() {
      axios
          .get(this.base_url + "/api/admindashboard/splywiseservicecomparison", {params: this.formData}, {token: this.$store.state.token})
          .then((res) => {
            if (res) {
              // console.log(res.data.splyWiseYearlyServiceComparison);
              // this.piChartOptions.labels.splice(0);
              // this.piSeries.splice(0);
              // var result = res.data.TTyWiseMonthlyServiceRatio;
              // for(var i=0; i<result.length; i++){
              //     this.piChartOptions.labels.push(result[i].TTYName);
              //     this.piSeries.push(parseInt(result[i].NoOfServices))
              // }
              // console.log(this.piSeries);
              // console.log(this.piChartOptions);
            }
          })
          .catch((err) => {
            console.log(err);
          });
    },
    yearWiseCompare() {
      let instance = this
      axios.get(this.base_url + "/api/admindashboard/yearwisecompare", {params: this.formData}, {token: this.$store.state.token}).then((res) => {
        console.log(res.data.data)
        // instance.barOptions = {
        //   chart: {
        //     id: 'basic-bar'
        //   },
        //   xaxis: {
        //     categories: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August','September','October','November','December']
        //   }
        // }
        instance.seriesBar = res.data.data
        // instance.seriesBar = [{
        //   name: '2021',
        //   data: [30, 40, 45, 50, 49, 60, 70, 91,5,7,8,12]
        // },{
        //   name: '2022',
        //   data: [31, 42, 40, 51, 35, 30, 32, 34,56,78,55,100]
        // }];
        console.log(instance.seriesBar)
      })
          .catch((err) => {
            console.log(err);
          });
    },
    moment: function (date) {
      return moment(date);
    },
    clearFilter() {
      this.formData.fromDate = '';
      this.formData.toDate = '';
      this.chartOptions.xaxis.categories.splice(0);
      this.series[0].data.splice(0);
      this.ttyWiseDailyServiceChart();
    },
    clearBarFilter() {
      this.formData.fromYear = '';
      this.formData.toYear = '';
      this.yearWiseCompare();
    },
    clearPiFilter() {
      this.formData.fromDate = '';
      this.piChartOptions.labels.splice(0);
      this.piSeries.splice(0);
      this.ttyWiseMonthlyServiceRatio();
    },
    currentMonth() {
      const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
      ];

      const d = new Date();
      return monthNames[d.getMonth()] + '-' + d.getFullYear();
    }
  },
  components: {
    'customer-care': CustomerCareDashboard,
    apexchart: VueApexCharts,
  },
}
</script>

<style scoped>
.button-border {
  border-color: #0042ff
}
</style>

const http = axios.create({
  baseURL: '/api',
  timeout: 1000,
});


var app = new Vue({
  el:'#app',
  data(){
    return {
      records:[],
      loading: false,
      editRecord: false,
      form: {
        date_start:  null,
        date_end:  null,
        price: 0.0
      }
    }
  },
  computed:{
    min(){
      return this.form.date_start;
    },
    max(){
      return this.form.date_end;
    }
  },
  methods:{
    reset(){
      this.form = {
        date_start:  null,
        date_end:  null,
        price: 0.0
      };
    },
    load(){
      this.loading = true;
      http.get('/').then(
        (response) => {
          this.records = response.data;
          this.loading = false;
        },
        (error) => {
          this.loading = false
          console.log({error});
        }
      );
    },
    add(){
      http.post('/', this.form).then(
        (response) => {
          this.reset();
          this.load();
        },
        (error) => {
          console.log({error});
        }
      )
    },
    edit(record){
      this.editRecord = Object.assign({}, record);
    },
    update(record){
      http.put('/',record).then(
        (response) => {
          this.load();
        },
        (error) => {
          console.log({error});
        }
      )
    },
    remove(record) {
      http.delete('/'+record.id).then(
        (response) => {
          this.load();
        },
        (error) => {
          console.log({error});
        }
      ) 
    },
    deleteAll(){
      http.delete('/all').then(
        (response) => {
          this.records = [];
        },
        (error) => {
          console.log({error});
        }
      ) 
    }
  },
  mounted(){
    this.load();
  }
})
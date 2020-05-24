<template>
  <div id="app">
    <header class="gr-header">
      <div>
        <h4 class="h h4 nmb">GovReader</h4>
        <p class="p nmb">An ATOM feed reader for UK Government press releases</p>
      </div>
      <div class="gr-header-right">
        <p class="p nmb">Feed Updated: {{feedUpdated}}</p>
      </div>
    </header>

    <div class="gr-frame">
      <div class="gr-departments">
        <h4 class="h h4">Departments</h4>
        <div class="gr-departments-flex" v-dragscroll>

          <department
            v-for="d in departments"
            :key="d.id"
            :name="d.name"
            :hex="d.hex"
            :currentActive="activeDepartment"
            @activate="setActiveDepartment">
          </department>

        </div>
      </div>

      <div class="gr-feed">
        <div class="gr-feed-articles">
          <div>
            <input type="search" v-model="search" placeholder="Search" @keydown.enter.prevent='null'>
            <div class="gr-feed-articles-flex">

              <template v-for="(a, i) in articles">

                <h3 class="h h3 gr-feed-articles-flex-heading" v-if="showDay(a, i)" :key="'date-' + a.id  + '-' + i">{{niceShortDate(a.updated) }}</h3>

                <div class="gr-article gr-card bg-light-grey" :style="{ 'border-color' : a.department.hex }" :key="a.id  + '-' + i">
                  <div class="gr-article-body">
                    <h6 class="h h6">{{a.department.name}}</h6>
                    <h5 class="h h5 mod-article-h">{{a.title}}</h5>
                    <p class="p">{{a.summary['#text']}}</p>
                    <a :href="a.link['-href']" class="button mod-co w-button" target="_blank" :style="{ 'border-color' : a.department.hex }">Read the story</a>
                  </div>
                  <div class="gr-article-footer">
                    <div class="gr-article-footer-date">Last Updated: {{niceFullDate(a.updated) }}</div>
                  </div>
                </div>

              </template>

            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>

    import Department from "./Department.vue";
    import moment from 'moment'
    import { dragscroll } from 'vue-dragscroll'

    export default {
        name: 'App',

        components: {
            'department' : Department
        },

        directives: {
            'dragscroll' : dragscroll
        },

        data(){
            return {
                feeds: [],
                activeDepartment: {},
                search: ''
            }
        },

        mounted() {

            const RSS_URL = 'http://sqdstaging.online/govreader/feed';

            fetch( RSS_URL )
                .then( response => this.feeds = response.json() )
                .then( data => this.feeds = data )

        },

        computed: {
            feedUpdated: function(){

                if ( !this.feeds.length ) {
                    return null
                }

                let dates = [];

                this.feeds.forEach( (f) => {
                    let date = moment(f.feed.updated).unix();
                    dates.push( date )
                } );

                return this.niceFullDate( moment.unix(Math.max(...dates)) )
            },

            articles: function(){
                let vm = this;

                if ( !this.feeds.length ) {
                    return null
                }

                let articles = [];

                this.feeds.forEach( (f) => {
                    f.feed.entry.forEach( (e) => {
                        e.department = {};
                        e.department['id'] = f.id;
                        e.department['hex'] = f.hex;
                        e.department['name'] = f.name;
                        articles.push(e)
                    } )
                } );

                if ( this.activeDepartment.id ) {
                    articles = articles.filter( article => article.department.id === this.activeDepartment.id )
                }

                if ( this.search ) {
                    articles = articles.filter( article => article.title.toLowerCase().indexOf( vm.search.toLowerCase() ) >= 0  )
                }

                return articles.sort( (a, b) => {
                    return moment(b.updated) - moment(a.updated)
                } )
            },

            departments: function() {

                if ( !this.feeds.length ) {
                    return null
                }

                let departments = [];

                this.feeds.forEach( (f) => {
                    departments.push(
                        {
                            id : f.id,
                            name : f.name,
                            hex: f.hex,
                        }
                    )
                } );

                return departments
            }
        },

        methods: {
            setActiveDepartment(dept){
                this.search = '';
                dept.id === this.activeDepartment.id ? this.activeDepartment = {} : this.activeDepartment = dept
            },

            niceFullDate(string){
                return moment(string).format('Do MMMM, YYYY. HH:mma')
            },

            niceShortDate(string){
                return moment(string).format('dddd, Do MMMM')
            },

            showDay(article, index){

                if ( index === 0 ) {
                    return true
                }

                let self = moment( article.updated );
                let prev = moment( this.articles[index - 1].updated );

                return self.day() !== prev.day()
            },
        },
    }

</script>

<style lang="scss">

  * {
    box-sizing: border-box;
  }

  body {
    font-family: 'Open Sans', sans-serif;
  }

  .h {
    margin-top: 0;
    margin-bottom: 20px;
    line-height: 1.25em;
    text-transform: none;

    &.nmb { margin-bottom: 0; }
    &.mod-article-h { min-height: 40px; }
    &.mod-article-h { min-height: 40px; }
  }

  .h.h1 { font-size: 34px; }
  .h.h2 { font-size: 28px; }
  .h.h3 { font-size: 22px; }
  .h.h4 { font-size: 18px; }
  .h.h5 { font-size: 16px; }

  .h.h6 {
    font-size: 14px;
    font-weight: 400;
  }

  .p {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 12px;
    font-weight: 400;

    &.nmb { margin-bottom: 0px; }
  }

  .button {
    padding: 0px 0px 5px;
    border-bottom: 2px solid #222;
    background-color: transparent;
    text-decoration: none;
    color: #222;
    font-size: 12px;
    line-height: 1em;
    font-weight: 600;

    &:hover {
      color: #444
    }
  }

  .gr-header {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    padding: 30px 50px;
    -webkit-box-pack: justify;
    -webkit-justify-content: space-between;
    -ms-flex-pack: justify;
    justify-content: space-between;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -ms-flex-align: center;
    align-items: center;
  }

  .gr-frame {
    padding: 0 50px;
  }

  .gr-departments {
    height: auto;
    padding-top: 30px;
    padding-bottom: 10px;
    border-top: 2px solid #d8d8d8;
    overflow: hidden;
    -webkit-transition: all 300ms cubic-bezier(.23, 1, .32, 1);
    transition: all 300ms cubic-bezier(.23, 1, .32, 1);

    .gr-departments-flex {
      display: -webkit-box;
      display: -webkit-flex;
      display: -ms-flexbox;
      display: flex;
      overflow: scroll;
      margin-top: -5px;
      margin-right: -15px;
      margin-left: -15px;
      padding: 25px 0;
      cursor: grab;

      &:active {
        cursor: grabbing;
      }

      &::-webkit-scrollbar-track {
        background-color: #eee;
      }

      &::-webkit-scrollbar {
        height: 8px;
      }

      &::-webkit-scrollbar-thumb {
        background-color: #ccc;
      }
    }

    &.mod-hidden {
      height: 0;
      padding: 0;
    }
  }

  .gr-card {
    padding: 20px;

    &.bg-light-grey { background-color: #eee; }
    &.bg-dark-grey { background-color: #666; }
  }

  .gr-feed {
    padding-top: 50px;
    padding-bottom: 30px;
    border-top: 2px solid #d8d8d8;

      input {
          width: 300px;
          display: block;
          margin-left: auto;
          border: none;
          border-bottom: 2px solid #d8d8d8;
          color: #222;
          font-size: 14px;
          line-height: 24px;
          font-weight: 600;
          text-align: right;
          margin-bottom: -70px;
          position: relative;
          z-index: 1;

          &:focus {
              outline: none;
              border-bottom-color: #222222;
          }
      }

    .gr-feed-articles {
      -webkit-box-flex: 1;
      -webkit-flex: auto;
      -ms-flex: auto;
      flex: auto;

      .gr-feed-articles-flex {
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        margin: -20px -15px 60px;
        -webkit-flex-wrap: wrap;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        -webkit-box-align: stretch;
        -webkit-align-items: stretch;
        -ms-flex-align: stretch;
        align-items: stretch;

        .gr-feed-articles-flex-heading {
          width: 100%;
          padding: 0 15px;
          margin: 40px 0 20px
        }
      }
    }
  }

  .gr-article {
    max-width: calc(33.34% - 30px);
    margin: 20px 15px;
    -webkit-box-flex: 1;
    -webkit-flex: 1 1 calc(33% - 30px);
    -ms-flex: 1 1 calc(33% - 30px);
    flex: 1 1 calc(33% - 30px);
    border-left: 8px solid #222;
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -webkit-flex-direction: column;
    -ms-flex-direction: column;
    flex-direction: column;
    -webkit-box-pack: justify;
    -webkit-justify-content: space-between;
    -ms-flex-pack: justify;
    justify-content: space-between;



    .gr-article-body {
      padding-bottom: 40px;
    }

    .gr-article-footer {
      margin-right: -20px;
      margin-bottom: -5px;
      margin-left: -20px;
      padding-top: 15px;
      padding-right: 20px;
      padding-left: 20px;
      -webkit-box-pack: justify;
      -webkit-justify-content: space-between;
      -ms-flex-pack: justify;
      justify-content: space-between;
      -webkit-box-align: center;
      -webkit-align-items: center;
      -ms-flex-align: center;
      align-items: center;
      border-top: 1px solid #d8d8d8;

      .gr-article-footer-date {
        font-size: 10px;
        line-height: 1em;
      }

      .gr-article-footer-link {
        color: #000;
        font-size: 12px;
        text-decoration: none;
      }
    }
  }

</style>
<template>

    <div
        class="gr-department gr-card bg-light-grey mod-co"
        :style="[ borderStyle, activeStyle ]"
        :class="{ 'mod-active' : active }"
        @click="activate">
        <h6 class="h h6">{{ name }}</h6>
    </div>

</template>

<script>

    export default {
        name: "Department",

        data(){
            return {
                id: this.$vnode.key,
                active: false
            }
        },

        props: {
            name: { type: String, default: '' },
            hex: { type: String, default: '' },
            currentActive: { type: Object, default: ()=>{ return {} } }
        },

        computed: {
            borderStyle: function() {
                return { 'border-color' : this.hex }
            },

            activeStyle: function() {
                return this.active ? { 'background-color' : this.hex } : null
            },
        },

        watch: {
            currentActive: function() {
                this.currentActive.id !== this.id ? this.passivate() : null
            }
        },

        methods: {
            activate(){
                if ( this.currentActive.id === this.id ){
                    this.$emit('activate', {} );
                    this.active = false 
                } else {
                    this.$emit('activate', { id : this.id } );
                    this.active = true
                }
            },
            passivate(){
                this.active = false
            }
        }
    }

</script>

<style lang="scss">

    .gr-department {
        position: relative;
        width: 160px;
        height: 220px;
        margin-right: 15px;
        margin-left: 15px;
        -webkit-box-flex: 0;
        -webkit-flex: 0 0 auto;
        -ms-flex: 0 0 auto;
        flex: 0 0 auto;
        border-left: 8px solid #222;
        -webkit-transition: all 300ms cubic-bezier(.23, 1, .32, 1);
        transition: all 300ms cubic-bezier(.23, 1, .32, 1);
        cursor: pointer;

        .h.h6 {
          user-select: none;
        }

        &:hover {
            -webkit-transform: translate(0px, -5px);
            -ms-transform: translate(0px, -5px);
            transform: translate(0px, -5px);
        }

        &.mod-active {
            .h.h6 {
                color: #fff;
                font-weight: 600;
                margin-bottom: 15px;
            }
        }
    }

</style>
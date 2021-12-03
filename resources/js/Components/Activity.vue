<template>
    <span class="activity" :class="stateClass"></span>
</template>

<script>
export default {
    props: {
        active: {
            type: Boolean,
            default: null
        }
    },
    computed: {
        stateClass() {
              if(this.active === null) {
                  return null;
              }

              return this.active ? 'activity__active' : 'activity__inactive';
        },
    }
}
</script>

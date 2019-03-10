<template>
  <div>
    <heading>{{__('List of recipients')}}</heading>

    <card>

      <div class="overflow-hidden overflow-x-auto relative">
        <!-- Resource Table -->
        <resource-table :resource-name="__('Recipients')"
                        :resources="recipients"
                        ref="resourceTable" />
      </div>
    </card>

  </div>
</template>

<script>
export default {

  data() {
    return {
      recipients: [],
      loading: true
    }
  },

  methods: {

    /**
     * Toggle selection of the recipient.
     */
    select(recipient) {
    },
  },

  /**
   * Set the list of available recipients.
   */
  async beforeRouteEnter(to, from, next) {
    const { data } = await axios.get('/nova-vendor/nova-one-signal/recipients-index')
    console.log(data)
    next(vm => vm.recipients = data)
  }
}
</script>


<style lang="sass" scoped>
.avatar
    height: 6rem
    width: 6rem
    flex: 6rem 0 0
    > img
      min-height: 100%
      min-width: 100%
</style>

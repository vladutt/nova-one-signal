<template>
  <div>
    <heading>{{__('Send a notification')}}</heading>

    <div class="py-6 px-8 w-full text-center">
      <p v-html="__(`You can add dynamic values from the current recipient data by including <strong>::recipient_model_key::</strong> in your content.`)"></p>
      <p v-html="__(`Example: <strong>Welcome ::firstname::!</strong> would send <strong>Welcome Yassi!</strong>`)"></p>
    </div>

    <card>
      <form @submit.prevent="send">

        <div>
          <field-wrapper>
            <div class="w-1/5 py-6 px-8">
              <form-label label-for="title">
                {{__('Title')}}
              </form-label>
            </div>
            <div class="py-6 px-8 w-full">
              <input class="w-full form-control form-input form-input-bordered"
                     v-model="notification.title"
                     id="title"
                     name="title"
                     :placeholder="`${__('Title')} (${__('optional')})`" />
            </div>
          </field-wrapper>
        </div>

        <div>
          <field-wrapper>
            <div class="w-1/5 py-6 px-8">
              <form-label label-for="subtitle">
                {{__('Subtitle')}}
              </form-label>
            </div>
            <div class="py-6 px-8 w-full">
              <input class="w-full form-control form-input form-input-bordered"
                     v-model="notification.subtitle"
                     id="subtitle"
                     name="subtitle"
                     :placeholder="`${__('Subtitle')} (${__('optional')})`" />
            </div>
          </field-wrapper>
        </div>

        <div>
          <field-wrapper>
            <div class="w-1/5 py-6 px-8">
              <form-label label-for="message">
                {{__('Message')}}
              </form-label>
            </div>
            <div class="py-6 px-8 w-full">
              <textarea class="w-full form-control form-input form-input-bordered py-3 h-auto"
                        v-model="notification.message"
                        id="message"
                        name="message"
                        :placeholder="__('Message')"
                        required></textarea>
            </div>
          </field-wrapper>
        </div>

        <div class="p-8">
          <div class="flex justify-between">
            <input class="form-control flex-1 form-input form-input-bordered mr-4"
                   v-model="filter"
                   :placeholder="__('Filter recipients...')" />
            <button class="btn mx-4 btn-default btn-primary"
                    @click="selectAll"
                    type="button">{{__('Select all')}}</button>
            <button class="btn ml-4 btn-default btn-danger"
                    @click="unselectAll"
                    type="button">{{__('Unselect all')}}</button>
          </div>

          <div class="flex flex-wrap m-8 overflow-auto text-center justify-center"
               style="max-height: 500px;">

            <template v-if="filteredRecipients.length > 0">
              <div class="rounded-full cursor-pointer font-bold bg-90 text-center avatar text-white flex items-center justify-center p-4 overflow-hidden relative m-2"
                   v-for="(recipient, index) in filteredRecipients"
                   :key="index"
                   :class="{ 'border-success border-4': isSelected(recipient) }"
                   @click="toggleSelection(recipient)">
                <span class="z-50">{{recipient[nameKey]}}</span>
                <img v-if="avatarKey"
                     :src="recipient[avatarKey]"
                     :alt="recipient[nameKey]"
                     class="opacity-75 absolute h-auto" />
              </div>
            </template>

            <template v-else>
              <h2>{{__('No matching recipients.')}}</h2>
            </template>

          </div>

          <p>{{__('Number of recipients:')}} <strong>{{notification.recipients.length}}/{{recipients.length}}</strong></p>
        </div>

        <div class="bg-30 flex px-8 py-4">
          <progress-button type="submit"
                           :disabled="loading || invalidNotification"
                           :processing="loading">
            {{ __('Send notification') }}
          </progress-button>
        </div>
      </form>
    </card>

  </div>
</template>

<script>
export default {

  data() {
    return {
      notification: {
        title: null,
        subtitle: null,
        message: null,
        recipients: [],
      },
      recipients: [],
      filter: null,
      loading: true,
      nameKey: null,
      avatarKey: null
    }
  },

  computed: {
    filteredRecipients() {
      return this.filter ? this.recipients.filter(p => p[this.nameKey].toLowerCase().includes(this.filter.toLowerCase())) : this.recipients
    },

    invalidNotification() {
      return !this.notification.message || this.notification.recipients.length < 1
    }
  },

  methods: {
    /**
     * Check whetehr the current recipient is selected.
     */
    isSelected(recipient) {
      return this.notification.recipients.includes(recipient)
    },

    /**
     * Toggle selection of the recipient.
     */
    toggleSelection(recipient) {
      if (this.isSelected(recipient)) {
        this.notification.recipients.splice(this.notification.recipients.indexOf(recipient), 1)
      } else {
        this.notification.recipients.push(recipient)
      }
    },

    /**
     * Select all recipients.
     */
    selectAll() {
      this.notification.recipients = this.recipients
    },

    /**
     * Unselect all recipients.
     */
    unselectAll() {
      this.notification.recipients = []
    },

    /**
     * Send the notification.
     */
    async send() {
      try {
        this.loading = true
        await axios.post('/nova-vendor/nova-one-signal/send', this.notification)
      } catch (error) {
        alert(error.response.data.message)
      } finally {
        this.loading = false
      }
    }
  },

  /**
   * Set the list of available recipients.
   */
  async beforeRouteEnter(to, from, next) {
    const { data: { list, name, avatar } } = await axios.get('/nova-vendor/nova-one-signal/authenticatables')
    next(vm => {
      vm.nameKey = name
      vm.avatarKey = avatar
      vm.recipients = list
      vm.loading = false
    })
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

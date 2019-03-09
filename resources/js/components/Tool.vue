<template>
  <div>
    <heading class="mb-6">Send a notification</heading>

    <card>
      <form @submit.prevent="send">
        <div class="py-6 px-8 w-full text-center">
          <p>You can add dynamic values from the current player data by including <strong>"::player_model_key::"</strong> in your content.</p>
          <p>Example: <strong>"Welcome ::firstname::!"</strong> would send <strong>"Welcome Yassi!"</strong></p>
        </div>

        <div>
          <field-wrapper>
            <div class="w-1/5 py-6 px-8">
              <form-label label-for="title">
                Title
              </form-label>
            </div>
            <div class="py-6 px-8 w-full">
              <input class="w-full form-control form-input form-input-bordered"
                     v-model="notification.title"
                     id="title"
                     name="title"
                     placeholder="Title (optional)" />
            </div>
          </field-wrapper>
        </div>

        <div>
          <field-wrapper>
            <div class="w-1/5 py-6 px-8">
              <form-label label-for="subtitle">
                Subtitle
              </form-label>
            </div>
            <div class="py-6 px-8 w-full">
              <input class="w-full form-control form-input form-input-bordered"
                     v-model="notification.subtitle"
                     id="subtitle"
                     name="subtitle"
                     placeholder="Subtitle (optional)" />
            </div>
          </field-wrapper>
        </div>

        <div>
          <field-wrapper>
            <div class="w-1/5 py-6 px-8">
              <form-label label-for="message">
                Message
              </form-label>
            </div>
            <div class="py-6 px-8 w-full">
              <textarea class="w-full form-control form-input form-input-bordered py-3 h-auto"
                        v-model="notification.message"
                        id="message"
                        name="message"
                        placeholder="Message"
                        required></textarea>
            </div>
          </field-wrapper>
        </div>

        <div class="p-8">
          <div class="flex justify-between">
            <input class="form-control flex-1 form-input form-input-bordered mr-4"
                   v-model="filter"
                   placeholder="Filter recipients..." />
            <button class="btn mx-4 btn-default btn-primary"
                    @click="selectAll"
                    type="button">Select all</button>
            <button class="btn ml-4 btn-default btn-danger"
                    @click="unselectAll"
                    type="button">Unselect all</button>
          </div>

          <div class="flex flex-wrap m-8 overflow-auto text-center justify-center"
               style="max-height: 300px;">

            <template v-if="filteredPlayers.length > 0">
              <div class="rounded-full cursor-pointer font-bold bg-90 text-center avatar text-white flex items-center justify-center p-4 overflow-hidden relative"
                   v-for="(player, index) in filteredPlayers"
                   :key="index"
                   :class="{ 'border-success border-4': isSelected(player) }"
                   @click="toggleSelection(player)">
                <span class="z-50">{{player[nameKey]}}</span>
                <img v-if="avatarKey"
                     :src="player[avatarKey]"
                     :alt="player[nameKey]"
                     class="opacity-75 absolute w-full h-auto" />
              </div>
            </template>

            <template v-else>
              <h2>No matching recipients.</h2>
            </template>

          </div>

          <p>Number of recipients: <strong>{{notification.players.length}}/{{players.length}}</strong></p>
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
        players: [],
      },
      players: [],
      filter: null,
      loading: true,
      nameKey: null,
      avatarKey: null
    }
  },

  computed: {
    filteredPlayers() {
      return this.filter ? this.players.filter(p => p[this.nameKey].toLowerCase().includes(this.filter.toLowerCase())) : this.players
    },

    invalidNotification() {
      return !this.notification.message || this.notification.players.length < 1
    }
  },

  methods: {
    /**
     * Check whetehr the current player is selected.
     */
    isSelected(player) {
      return this.notification.players.includes(player)
    },

    /**
     * Toggle selection of the player.
     */
    toggleSelection(player) {
      if (this.isSelected(player)) {
        this.notification.players.splice(this.notification.players.indexOf(player), 1)
      } else {
        this.notification.players.push(player)
      }
    },

    /**
     * Select all recipients.
     */
    selectAll() {
      this.notification.players = this.players
    },

    /**
     * Unselect all recipients.
     */
    unselectAll() {
      this.notification.players = []
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
   * Set the list of available players.
   */
  async beforeRouteEnter(to, from, next) {
    const { data: { list, name, avatar } } = await axios.get('/nova-vendor/nova-one-signal/authenticatables')
    next(vm => {
      vm.nameKey = name
      vm.avatarKey = avatar
      vm.players = list
      vm.loading = false
    })
  }
}
</script>


<style lang="sass" scoped>
.avatar
    height: 6rem
    width: 6rem
</style>

Nova.booting((Vue, router, store) => {
    router.addRoutes([{
        name: 'nova-one-signal-send',
        path: '/nova-one-signal/send',
        component: require('./components/Send'),
    }, {
        name: 'nova-one-signal-list-recipients',
        path: '/nova-one-signal/list-recipients',
        component: require('./components/ListRecipients'),
    }])
})
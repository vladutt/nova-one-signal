Nova.booting((Vue, router, store) => {
    router.addRoutes([
        {
            name: 'nova-one-signal',
            path: '/nova-one-signal',
            component: require('./components/Tool'),
        },
    ])
})

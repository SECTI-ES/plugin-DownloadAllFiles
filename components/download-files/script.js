app.component("download-files", {
    template: $TEMPLATES["download-files"],

    // props: {
    //     entity: {
    //         type: Entity,
    //         required: false,
    //     },
    // },

    data() {
        return {
            downloading: null,
        };
    },

    methods: {
        async downloadAll() {
            downloading = true;
            console.log("iniciou");
            await new Promise((resolve) => setTimeout(resolve, 2000));
            console.log("concluiu");
            downloading = false;
        },
    },
});

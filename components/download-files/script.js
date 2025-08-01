app.component("download-files", {
    template: $TEMPLATES["download-files"],

    props: {
        entity: {
            type: Object,
            required: false,
        },
    },

    data() {
        return {
            downloading: null,
        };
    },

    methods: {
        async downloadAll() {
            this.downloading = true;

            const apiUrl = Utils.createUrl(
                "download-registration",
                "createAllZipFiles",
                {
                    opportunityId: this.entity.id,
                },
            );

            window.open(apiUrl, "_blank");
            this.downloading = false;
        },
    },
});

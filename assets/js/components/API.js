const API = {
    dashboard: {
        widgets: {
            fetch: 'admin/dashboard/widget',
            save: 'admin/dashboard/widget/save',
            body: 'admin/dashboard/widget/body',
            config: 'admin/dashboard/widget/config',
        }
    },
    session: {
        create: 'admin/session',
        destroy: (token) => `admin/session/${token}/destroy`,
        heartbeat: (token) => `admin/session/${token}/heartbeat`,
        inactive: (token) => `admin/session/${token}/inactive`,
    }
};

export default API;

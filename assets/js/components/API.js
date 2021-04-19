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
        heartbeat: 'admin/session/heartbeat',
        interact: 'admin/session/interact',
    }
};

export default API;

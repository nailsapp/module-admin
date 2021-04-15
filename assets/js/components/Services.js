import axios from 'axios';

const services = {
    apiRequest(options) {
        let request = {
            method: options.method,
            url: `${window.SITE_URL}api/${options.url}`,
            [options.method === 'get' ? 'params' : 'data']: options.params ? options.params : options.data,
            headers: options.headers ? {[options.headers.key]: options.headers.value} : {}
        };
        return axios(request)
            .then((response) => {
                return response;
            });
    }
};

export default services;

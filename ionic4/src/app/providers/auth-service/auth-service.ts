import {Injectable} from '@angular/core';
import {Http, Headers} from '@angular/http';

const apiUrl = 'http://localhost:8888';

@Injectable()
export class AuthService {

    constructor(public http: Http) {
    }

    static setToken(credentials, token) {
        localStorage.setItem('token', token.access_token);
        localStorage.setItem('token_expires', token.expires_in);
        localStorage.setItem('client_id', credentials.username);
    }

    login(credentials) {
        return new Promise((resolve, reject) => {
            const headers = new Headers();
            // headers.append('Content-Type', 'application/json');
            headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
            headers.append('Access-Control-Allow-Origin', '*');
            headers.append('Authorization', 'Basic ' + btoa(credentials.username + ':' + credentials.password));

            this.http.post(apiUrl + '/oauth', 'grant_type=client_credentials', {headers: headers})
                .subscribe(result => {
                    AuthService.setToken(credentials, result.json());
                    resolve(result.json());
                }, (err) => {
                    reject(err.json());
                });
        });
    }

    logout() {
        return new Promise((resolve, reject) => {
            // let headers = new Headers()
            // headers.append('X-Auth-Token', localStorage.getItem('token'))

            this.http.post(apiUrl + '/oauth/revoke', {
                token: localStorage.getItem('token'),
                token_type_hint: 'access_token'
            })
                .subscribe(res => {
                    localStorage.clear();
                    resolve(res.json());
                }, (err) => {
                    reject(err);
                });
        });
    }

}

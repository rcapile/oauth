import { Injectable } from '@angular/core'
import { Http, Headers } from '@angular/http'
import 'rxjs/add/operator/map'

let apiUrl = 'http://localhost:8888/oauth'

@Injectable()
export class AuthService {

  constructor (public http: Http) {}

  login (credentials) {
    return new Promise((resolve, reject) => {
      let headers = new Headers()
            //headers.append('Content-Type', 'application/json');
      headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8')
      headers.append('Access-Control-Allow-Origin', '*')
      headers.append('Authorization', 'Basic ' + btoa(credentials.username + ':' + credentials.password))

      this.http.post(apiUrl, 'grant_type=client_credentials', {headers: headers})
        .subscribe(res => {
          resolve(res.json())
        }, (err) => {
          reject(err)
        })
    })
  }

  logout () {
    return new Promise((resolve, reject) => {
      //let headers = new Headers()
      //headers.append('X-Auth-Token', localStorage.getItem('token'))

      this.http.post(apiUrl + '/revoke', {
          token: localStorage.getItem('token'),
          token_type_hint: 'access_token'
        })
        .subscribe(res => {
          resolve(res.json())
        }, (err) => {
          reject(err)
        })
    })
  }

}

import {Component, OnInit} from '@angular/core';
import {LoadingController, NavController, ToastController} from '@ionic/angular';
import {AuthService} from '../../providers/auth-service/auth-service';

@Component({
    selector: 'app-login',
    templateUrl: './login.page.html',
    styleUrls: ['./login.page.scss'],
})
export class LoginPage implements OnInit {

    loading: any;
    loginData = {username: '', password: ''};
    data: any;

    constructor(
        public navCtrl: NavController,
        public authService: AuthService,
        public loadingCtrl: LoadingController,
        private toastCtrl: ToastController) {
    }

    ngOnInit() {
    }

    doLogin() {
        this.showLoader().then(() => {
            this.authService.login(this.loginData).then((result) => {
                console.log(this.loading);
                this.loading.dismiss();
                this.data = result;
                localStorage.setItem('token', this.data.access_token);
                this.navCtrl.goRoot('tabs');
            }, (err) => {
                this.loading.dismiss();
                this.presentToast(err);
            });
        });
    }

    async showLoader() {
        this.loading = await this.loadingCtrl.create({
            content: 'Authenticating...'
        });

        await this.loading.present();
    }

    presentToast(msg) {
        console.log(msg);
        this.toastCtrl.create({
            message: msg,
            duration: 3000,
            position: 'bottom'
        }).then(toast => toast.present());

        /*toast.onDidDismiss(() => {
            console.log('Dismissed toast');
        });*/
    }
}

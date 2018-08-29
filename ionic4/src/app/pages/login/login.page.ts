import {Component, OnInit} from '@angular/core';
import {LoadingController, NavController, ToastController} from '@ionic/angular';
import {AuthService} from '../../providers/auth-service/auth-service';

@Component({
    selector: 'app-login',
    templateUrl: './login.page.html',
    styleUrls: ['./login.page.scss'],
})
export class LoginPage {

    loading: any;
    loginData = {username: '', password: ''};

    constructor(
        public navCtrl: NavController,
        public authService: AuthService,
        public loadingCtrl: LoadingController,
        private toastCtrl: ToastController) {
    }

    doLogin() {
        this.showLoader().then(() => {
            this.authService.login(this.loginData).then(() => {
                this.loading.dismiss();
                this.navCtrl.goRoot('/tabs/(home:home)');
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
        this.toastCtrl.create({
            message: msg.detail,
            duration: 3000,
            position: 'bottom'
        }).then(toast => toast.present());
    }
}

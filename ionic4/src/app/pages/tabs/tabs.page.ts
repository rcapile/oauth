import {Component} from '@angular/core';
import {App, LoadingController, NavController, ToastController} from '@ionic/angular';
import {AuthService} from '../../providers/auth-service/auth-service';

@Component({
    selector: 'app-tabs',
    templateUrl: 'tabs.page.html',
    styleUrls: ['tabs.page.scss']
})
export class TabsPage {

  /*  constructor(public navCtrl: NavController) {
        if (!localStorage.getItem('token')) {
            navCtrl.goRoot('login');
        }
    }
*/
    loading: any;
    isLoggedIn = false;

    constructor(public app: App,
                public navCtrl: NavController,
                public authService: AuthService,
                public loadingCtrl: LoadingController,
                private toastCtrl: ToastController) {
        if (localStorage.getItem('token')) {
            this.isLoggedIn = true;
        }
    }

    logout() {
        this.showLoader().then(() => {
            this.authService.logout().then((result) => {
                this.loading.dismiss();
                localStorage.clear();
                this.navCtrl.goRoot('login');
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
            message: msg,
            duration: 3000,
            position: 'bottom'
        }).then(toast => toast.present());
        /* toast.onDidDismiss(() => {
             console.log('Dismissed toast');
         });*/
    }

}

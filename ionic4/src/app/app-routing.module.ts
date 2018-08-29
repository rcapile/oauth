import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';

const routes: Routes = [
    {path: 'login', loadChildren: './pages/login/login.module#LoginPageModule'},
    {path: '', loadChildren: './pages/tabs/tabs.module#TabsPageModule'}
];

@NgModule({
    imports: [RouterModule.forRoot(
        routes,
        {enableTracing: false} // <-- debugging purposes only
    )],
    exports: [RouterModule]
})

export class AppRoutingModule {
}

<?php

namespace App\Livewire;

use Livewire\Component;

class SadminDashboard extends Component
{
    public $activeUsersCount,$deActiveUsersCount,$activeVcard,$deActiveVcard,$activeOrganisationsCount,$deActiveOrganisationsCount,$activeOrganisationUsersCount,$deActiveOrganisationUsersCount;
    public function mount($activeUsersCount,$deActiveUsersCount,$activeVcard,$deActiveVcard,$activeOrganisationsCount,$deActiveOrganisationsCount,$activeOrganisationUsersCount,$deActiveOrganisationUsersCount){
         $this->activeUsersCount = $activeUsersCount;
         $this->deActiveUsersCount = $deActiveUsersCount;
         $this->activeVcard = $activeVcard;
         $this->deActiveVcard = $deActiveVcard;
         $this->activeOrganisationsCount = $activeOrganisationsCount;
         $this->deActiveOrganisationsCount = $deActiveOrganisationsCount;
         $this->activeOrganisationUsersCount = $activeOrganisationUsersCount;
         $this->deActiveOrganisationUsersCount = $deActiveOrganisationUsersCount;
    }
    public function placeholder(){
         return view('lazy_loading.sadmin-dashboard');
    }
    public function render()
    {
        return view('livewire.sadmin-dashboard');
    }
}

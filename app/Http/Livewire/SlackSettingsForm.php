<?php

namespace App\Http\Livewire;

use GuzzleHttp\Client;
use Livewire\Component;
use App\Models\Setting;

class SlackSettingsForm extends Component
{
    public $slack_endpoint;
    public $slack_channel;
    public $slack_botname;

    public Setting $setting;

    public function mount(){

        $this->setting = Setting::getSettings();
        $this->slack_endpoint = $this->setting->slack_endpoint;
        $this->slack_channel = $this->setting->slack_channel;
        $this->slack_botname = $this->setting->slack_botname;

    }

    public function render()
    {
        return view('livewire.slack-settings-form');
    }

    public function testSlack(){

        // If validation passes, continue to the curl request
        $slack = new Client([
            'base_url' => e($this->slack_endpoint),
            'defaults' => [
                'exceptions' => false,
            ],
        ]);

        $payload = json_encode(
            [
                'channel'    => e($this->slack_channel),
                'text'       => trans('general.slack_test_msg'),
                'username'    => e($this->slack_botname),
                'icon_emoji' => ':heart:',
            ]);

        try {
            $slack->post($this->slack_endpoint, ['body' => $payload]);
            return session()->flash('success' , 'Your Slack Integration works!');

        } catch (\Exception $e) {
            return session()->flash('error' , 'Please check the channel name and webhook endpoint URL ('.e($this->slack_endpoint).'). Slack responded with: '.$e->getMessage());
        }

        //}
        return session()->flash('message' , 'Something went wrong :( ');



    }
    public function submit()
    {
        $this->validate([
            'slack_endpoint'                      => 'url|required_with:slack_channel|starts_with:https://hooks.slack.com/|nullable',
            'slack_channel'                       => 'required_with:slack_endpoint|starts_with:#|nullable',
            'slack_botname'                       => 'string|nullable',
        ]);

        $this->setting->slack_endpoint = $this->slack_endpoint;
        $this->setting->slack_channel = $this->slack_channel;
        $this->setting->slack_botname = $this->slack_botname;

        $this->setting->save();

        session()->flash('save',trans('admin/settings/message.update.success'));


    }
}

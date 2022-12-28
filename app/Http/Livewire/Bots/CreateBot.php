<?php

namespace App\Http\Livewire\Bots;

use App\Models\Bot;
use App\Rules\BotLimits;
use Livewire\Component;
use Illuminate\Validation\Rule;

class CreateBot extends Component
{
    use WithValidation;

    public $title = 'Bots';
    public $bot_limits;

    public function render()
    {
        $exchange_count = auth()->user()->exchanges()->count();
        if ($exchange_count == 0) {
            session()->flash('message', 'You must create an exchange before a Bot.');

            redirect(route('exchanges.add'));
        }
        $rederData = $this->renderData();

        return view('livewire.bots.create-bot', $rederData)->layoutData([
            'title' => $this->title,
        ]);
    }

    public function mount()
    {
        $this->bot = new Bot;
        $this->clearForm();
        $this->rules['bot_limits'] = 'bot_limits';
        $this->rules['bot.symbol'] = [
            'required|string|max:12',
            Rule::unique('bots')->ignore(auth()->user()->id),
        ];
    }

    public function clearForm()
    {
        $this->bot->symbol = '';
        $this->bot->market_type = 'futures';
        $this->bot->grid_mode = 'recursive';
        $this->bot->grid_id = '';
        $this->bot->exchange_id = 'bybit';
        $this->bot->assigned_balance = 0;
        $this->bot->lm = 'n';
        $this->bot->lwe = 0.2;
        $this->bot->sm = 'm';
        $this->bot->swe = 0.15;
        $this->bot->leverage = 7;
    }

    public function submit()
    {
        $this->validate();

        $this->bot->symbol = strtoupper($this->bot->symbol);
        $this->validate([
            'bot.symbol' => [Rule::unique('bots', 'symbol')->ignore(auth()->user()->id)],
            'bot_limits' => [new BotLimits],
        ]);

        $this->bot->user_id = request()->user()->id;
        if($this->bot->grid_id == 'null' || $this->bot->grid_id == '')
            $this->bot->grid_id = null;
        $this->bot->save();

        session()->flash('message', 'Bot successfully created.');
        // session()->flash('status', 'bot-created');

        $this->clearForm();

        return redirect()->route('bots.index');
    }
}

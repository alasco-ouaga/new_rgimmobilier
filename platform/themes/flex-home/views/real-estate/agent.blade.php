@php
    if ($account->phone) {
        Theme::set('hotlineNumber', $account->phone);
    }
@endphp
<section class="main-homes">
    <div class="bgheadproject hidden-xs" style="background: url('{{ theme_option('breadcrumb_background') ? RvMedia::url(theme_option('breadcrumb_background')) : Theme::asset()->url('images/banner-du-an.jpg') }}')">
        <div class="description">
            <div class="container-fluid w90">
                <h1 class="text-center">{{ $account->name }}</h1>
                <p class="text-center">{{ $account->description }}</p>
                {!! Theme::partial('breadcrumb') !!}
            </div>
        </div>
    </div>
    <div class="container-fluid w90 padtop30">
        <div class="rowm10">
            <h5 class="headifhouse">{{ __('Agent info') }}</h5>
            <div class="agent-details">
                <div>
                    @if ($account->username)
                        <a href="{{ route('public.agent', $account->username) }}">
                            @if ($account->avatar->url)
                                <img src="{{ RvMedia::getImageUrl($account->avatar->url, 'thumb') }}" alt="{{ $account->name }}" class="img-thumbnail">
                            @else
                                <img src="{{ $account->avatar_url }}" alt="{{ $account->name }}" class="img-thumbnail">
                            @endif
                        </a>
                    @else
                        @if ($account->avatar->url)
                            <img src="{{ RvMedia::getImageUrl($account->avatar->url, 'thumb') }}" alt="{{ $account->name }}" class="img-thumbnail">
                        @else
                            <img src="{{ $account->avatar_url }}" alt="{{ $account->name }}" class="img-thumbnail">
                        @endif
                    @endif
                </div>
                <div>
                    <h4>{{ $account->name }}</h4>
                    @if ($account->email && ! setting('real_estate_hide_agency_email', 0))
                        <p><strong class="d-inline-block">{{ __('Email') }}</strong>: <span dir="ltr"><a href="mailto:{{ $account->email }}">{{ $account->email }}</a></span></p>
                    @endif
                    @if ($account->phone && ! setting('real_estate_hide_agency_phone', 0))
                        <p class="mobile"><strong class="d-inline-block">{{ __('Phone') }}</strong>: <span dir="ltr"><a
                                    href="tel:{{ $account->phone }}">{{ $account->phone }}</a></span></p>
                    @endif
                    <p><strong class="d-inline-block">{{ __('Joined on') }}</strong>: <span class="d-inline-block">{{ $account->created_at->toDateString() }}</span></p>
                </div>
                <div style="clear: both"></div>
            </div>

            <div style="clear: both"></div>

            <div style="margin-top: 20px" class="ck-content">{!! BaseHelper::clean($account->description) !!}</div>

            <br>

            @if ($properties->count())
                <h5 class="headifhouse">{{ __('Properties by this agent') }}</h5>
                <div class="projecthome px-2">
                    <div class="row rowm10">
                        @foreach ($properties as $property)
                            <div class="col-sm-6 col-lg-4 col-xl-3 colm10">
                                {!! Theme::partial('real-estate.properties.item', ['property' => $property]) !!}
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <span class="item text-danger fw-bold fst-italic ">
                    Aucune publication de propriété pour cet agent
                </span>
            @endif
        </div>
    </div>
</section>
<br>
<div class="col-sm-12">
    <nav class="d-flex justify-content-center pt-3" aria-label="Page navigation example">
        {!! $properties->withQueryString()->links() !!}
    </nav>
</div>
<br>
<br>

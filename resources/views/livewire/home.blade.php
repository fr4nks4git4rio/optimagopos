@section('title', 'Inicio')
@push('styles')
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">--}}
<style>
    .info-box {
        box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
        border-radius: 0.25rem;
        background-color: #fff;
        display: -ms-flexbox;
        display: flex;
        margin-bottom: 1rem;
        min-height: 60px;
        padding: 0.5rem;
        position: relative;
        width: 100%;
    }

    .info-box .info-box-icon {
        border-radius: 0.25rem;
        -ms-flex-align: center;
        align-items: center;
        display: -ms-flexbox;
        display: flex;
        font-size: 1.875rem;
        -ms-flex-pack: center;
        justify-content: center;
        text-align: center;
        width: 50px;
        height: 50px;
    }

    .bg-info,
    .bg-info>a {
        color: #fff !important;
    }

    .bg-info {
        background-color: #17a2b8 !important;
    }

    .elevation-1 {
        box-shadow: 0 1px 3px rgba(0, 0, 0, .12), 0 1px 2px rgba(0, 0, 0, .24) !important;
    }

    .info-box .info-box-content {
        display: -ms-flexbox;
        display: flex;
        -ms-flex-direction: column;
        flex-direction: column;
        -ms-flex-pack: center;
        justify-content: center;
        line-height: 1.8;
        -ms-flex: 1;
        flex: 1;
        padding: 0 10px;
        overflow: hidden;
    }

    .info-box .info-box-content {
        display: -ms-flexbox;
        display: flex;
        -ms-flex-direction: column;
        flex-direction: column;
        -ms-flex-pack: center;
        justify-content: center;
        line-height: 1.8;
        -ms-flex: 1;
        flex: 1;
        padding: 0 10px;
        overflow: hidden;
    }

    .info-box .info-box-text,
    .info-box .progress-description {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .info-box .info-box-number {
        display: block;
        margin-top: 0.25rem;
        font-weight: 700;
    }
</style>
@endpush

<div class="row">
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
@endpush

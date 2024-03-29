<div class="row videos">
    <div class="col-md-12 mb-3">
        <div class="form-group">
            <label for="" class="form-label">
                @if($required == 'required')
                    <strong class="text-danger">*</strong>
                @endif
                <strong>{{ $label }}</strong>
                @if($tooltipTitle != null)
                    <span class="text-info" data-toggle="tooltip" data-placement="{{ $tooltipPlacement ?? 'top' }}" title="{{ $tooltipTitle }}">
                        <i class="fa fa-info-circle"></i>
                    </span>
                @endif
            </label>
            <div class="custom-file">

                <input type="file"
                       class="video_upload custom-file-input @error( $name ) is-invalid @enderror"
                       data-upload="{{ $upload_url }}"
                       data-remove="{{ $remove_url }}"
                       data-name="{{ $name }}"
                       @if($disabled == 'disabled') disabled @endif
                >
                <label class="custom-file-label" for="">بارگذاری ویدیو</label>

                @error( $name )
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

                <span class="invalid-feedback d-none" role="alert">
                    <strong></strong>
                </span>

            </div>
        </div>

        @if(! is_null($validation))
            <input type="hidden"
                   class="custom_validation"
                   name="validation"
                   value="{{ $validation }}"
            >
        @endif
        @if(! is_null($disk))
            <input type="hidden"
                   class="custom_disk"
                   name="disk"
                   value="{{ $disk }}"
            >
        @endif

    </div>
    <div class="col-md-12 video_files">
        @if( old($name) )
            @foreach( old($name) as $old_video )
                @php $last_video = config('attachment.media_model')::query()->find($old_video); @endphp
                <div class="video_file_upload mb-2">
                    <div class="file_info">
                        <a href="{{ $last_video->getUrl() }}">
                            <span class="file_name">{{ $last_video->basename }}</span>
                        </a>
                        <input class="uploaded_file_path" type="hidden" name="{{ $name . "[]" }}" value="{{ $last_video->getKey() }}">
                    </div>
                    <div class="file_caption rtl my-1">
                        <div class="row">
                            <div class="col">
                                <label>عنوان</label>
                                <input type="text" class="form-control" name="{{ $name . "_caption[]" }}" value="{{ $last_video->caption }}">
                            </div>
                        </div>
                    </div>
                    <span class="delete_file"><i class="fa fa-times"></i></span>
                </div>
            @endforeach
        @endif
    </div>
</div>

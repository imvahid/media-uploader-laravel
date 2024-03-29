<div class="row attachments">
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
                       class="attachment_upload custom-file-input @error( $name ) is-invalid @enderror"
                       data-upload="{{ $upload_url }}"
                       data-remove="{{ $remove_url }}"
                       data-name="{{ $name }}"
                       multiple="multiple"
                       @if($disabled) disabled @endif
                >
                <label class="custom-file-label" for="">بارگذاری فایل ضمیمه</label>

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
    <div class="col-md-12 uploaded_files">
        @if( old($name) )
            @foreach( old($name) as $old_attachment )
                @php $last_attachment = config('attachment.media_model')::query()->find($old_attachment); @endphp
                <div class="attachment_file_upload mb-2">
                    <div class="file_info">
                        @if(in_array(config('filesystems.disks.' . $last_attachment->disk . '.driver'), ['ftp', 'sftp']))
                            @php
                                $file_url = config('filesystems.disks.' . $last_attachment->disk . '.protocol')  . '://' . config('filesystems.disks.' . $last_attachment->disk . '.host') . '/' .  $last_attachment->getDiskPath();
                            @endphp
                        @elseif(in_array(config('filesystems.disks.' . $last_attachment->disk . '.driver'), ['s3']))
                            @php
                                $file_url = 'https://' . config('filesystems.disks.' . $last_attachment->disk . '.bucket') .
                                    '.' . config('filesystems.disks.' . $last_attachment->disk . '.region') .
                                    '/' . $last_attachment->getDiskPath();
                            @endphp
                        @elseif(config('filesystems.disks.' . $last_attachment->disk . '.driver') == 'local' && config('filesystems.disks.' . $last_attachment->disk . '.visibility') == 'private')
                            @php
                                $file_url = \Illuminate\Support\Facades\URL::temporarySignedRoute('download.attachment', now()->addHours(config('attachment.attachment_download_link_expire_time')), ['path' => $last_attachment->getDiskPath()]);
                            @endphp
                        @else
                            @php
                                $file_url = $last_attachment->getUrl();
                            @endphp
                        @endif
                        <a href="{{ $file_url }}">
                            <span class="file_name">{{ $last_attachment->basename }}</span>
                        </a>
                        <input class="uploaded_file_path" type="hidden" name="{{ $name . "[]" }}" value="{{ $last_attachment->getKey() }}">
                    </div>

                    <span class="delete_file"><i class="fa fa-times"></i></span>
                </div>
            @endforeach
        @endif
    </div>
</div>

@extends('layouts.admin.index')
@section('content')
    <section class="content">
        <form action="{{ route('admin.configs.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <!-- Cột trái -->
                <div class="col-md-6">
                    <!-- Thông tin cơ bản website -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Thông tin cơ bản website</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="site_name">Tên website</label>
                                <input type="text" name="site_name" class="form-control"
                                    value="{{ old('site_name', $config?->site_name) }}">
                            </div>
                            <div class="form-group">
                                <label for="url">URL</label>
                                <input type="url" name="url" class="form-control"
                                    value="{{ old('url', $config?->url) }}">
                            </div>
                            <div class="form-group">
                                <label for="domain">Domain</label>
                                <input type="text" name="domain" class="form-control"
                                    value="{{ old('domain', $config?->domain) }}">
                            </div>
                            <div class="form-group">
                                <label for="description">Mô tả</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $config?->description) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="keywords">Từ khóa</label>
                                <textarea name="keywords" class="form-control" rows="2">{{ old('keywords', $config?->keywords) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="author">Tác giả</label>
                                <input type="text" name="author" class="form-control"
                                    value="{{ old('author', $config?->author) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Theme và Giao diện -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Theme và Giao diện</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="theme_color">Màu chủ đạo</label>
                                <input type="color" name="theme_color" class="form-control"
                                    value="{{ old('theme_color', $config?->theme_color) }}">
                            </div>
                            <div class="form-group">
                                <label for="favicon">Favicon</label>
                                <input type="file" name="favicon" class="form-control">
                                @if ($config?->favicon)
                                    <img src="{{ $config->favicon }}" class="mt-2" style="max-height: 32px">
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="og_image">OG Image</label>
                                <input type="file" name="og_image" class="form-control">
                                @if ($config?->og_image)
                                    <img src="{{ $config->og_image }}" class="mt-2" style="max-height: 100px">
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="no_thumb_image">No Thumb Image</label>
                                <input type="file" name="no_thumb_image" class="form-control">
                                @if ($config?->no_thumb_image)
                                    <img src="{{ $config->no_thumb_image }}" class="mt-2" style="max-height: 100px">
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Blog Settings -->
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Cài đặt Blog</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="related_posts_num">Số bài viết liên quan</label>
                                <input type="number" name="related_posts_num" class="form-control"
                                    value="{{ old('related_posts_num', $config?->related_posts_num) }}">
                            </div>
                            <div class="form-group">
                                <label for="posts_per_page">Số bài viết mỗi trang</label>
                                <input type="number" name="posts_per_page" class="form-control"
                                    value="{{ old('posts_per_page', $config?->posts_per_page) }}">
                            </div>
                            <div class="form-group">
                                <label for="comments_system">Hệ thống bình luận</label>
                                <select name="comments_system" class="form-control">
                                    <option value="blogger"
                                        {{ $config?->comments_system == 'blogger' ? 'selected' : '' }}>Blogger</option>
                                    <option value="disqus" {{ $config?->comments_system == 'disqus' ? 'selected' : '' }}>
                                        Disqus</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="disqus_shortname">Disqus Shortname</label>
                                <input type="text" name="disqus_shortname" class="form-control"
                                    value="{{ old('disqus_shortname', $config?->disqus_shortname) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cột phải -->
                <div class="col-md-6">
                    <!-- Social Media -->
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">Social Media</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="facebook_author">Facebook Author</label>
                                <input type="text" name="facebook_author" class="form-control"
                                    value="{{ old('facebook_author', $config?->facebook_author) }}">
                            </div>
                            <div class="form-group">
                                <label for="facebook_page">Facebook Page</label>
                                <input type="text" name="facebook_page" class="form-control"
                                    value="{{ old('facebook_page', $config?->facebook_page) }}">
                            </div>
                            <div class="form-group">
                                <label for="fb_app_id">Facebook App ID</label>
                                <input type="text" name="fb_app_id" class="form-control"
                                    value="{{ old('fb_app_id', $config?->fb_app_id) }}">
                            </div>
                            <div class="form-group">
                                <label for="fb_admin_id">Facebook Admin ID</label>
                                <input type="text" name="fb_admin_id" class="form-control"
                                    value="{{ old('fb_admin_id', $config?->fb_admin_id) }}">
                            </div>
                            <div class="form-group">
                                <label for="twitter_creator">Twitter Creator</label>
                                <input type="text" name="twitter_creator" class="form-control"
                                    value="{{ old('twitter_creator', $config?->twitter_creator) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Text UI -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Text UI</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="show_more_text">Show More Text</label>
                                <input type="text" name="show_more_text" class="form-control"
                                    value="{{ old('show_more_text', $config?->show_more_text) }}">
                            </div>
                            <div class="form-group">
                                <label for="follow_by_email_text">Follow By Email Text</label>
                                <input type="text" name="follow_by_email_text" class="form-control"
                                    value="{{ old('follow_by_email_text', $config?->follow_by_email_text) }}">
                            </div>
                            <div class="form-group">
                                <label for="related_posts_text">Related Posts Text</label>
                                <input type="text" name="related_posts_text" class="form-control"
                                    value="{{ old('related_posts_text', $config?->related_posts_text) }}">
                            </div>
                            <div class="form-group">
                                <label for="load_more_text">Load More Text</label>
                                <input type="text" name="load_more_text" class="form-control"
                                    value="{{ old('load_more_text', $config?->load_more_text) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Cookie Consent -->
                    <div class="card card-danger">
                        <div class="card-header">
                            <h3 class="card-title">Cookie Consent</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="cookie_message">Cookie Message</label>
                                <textarea name="cookie_message" class="form-control" rows="2">{{ old('cookie_message', $config?->cookie_message) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="cookie_accept_text">Cookie Accept Text</label>
                                <input type="text" name="cookie_accept_text" class="form-control"
                                    value="{{ old('cookie_accept_text', $config?->cookie_accept_text) }}">
                            </div>
                            <div class="form-group">
                                <label for="cookie_learn_more_text">Cookie Learn More Text</label>
                                <input type="text" name="cookie_learn_more_text" class="form-control"
                                    value="{{ old('cookie_learn_more_text', $config?->cookie_learn_more_text) }}">
                            </div>
                            <div class="form-group">
                                <label for="cookie_policy_url">Cookie Policy URL</label>
                                <input type="text" name="cookie_policy_url" class="form-control"
                                    value="{{ old('cookie_policy_url', $config?->cookie_policy_url) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Feature Flags -->
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Tính năng</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enable_rss"
                                        name="enable_rss" {{ old('enable_rss', $config?->enable_rss) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="enable_rss">RSS</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enable_adsense"
                                        name="enable_adsense"
                                        {{ old('enable_adsense', $config?->enable_adsense) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="enable_adsense">Google Adsense</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enable_twitter"
                                        name="enable_twitter"
                                        {{ old('enable_twitter', $config?->enable_twitter) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="enable_twitter">Twitter</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enable_youtube"
                                        name="enable_youtube"
                                        {{ old('enable_youtube', $config?->enable_youtube) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="enable_youtube">YouTube</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enable_instagram"
                                        name="enable_instagram"
                                        {{ old('enable_instagram', $config?->enable_instagram) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="enable_instagram">Instagram</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enable_pinterest"
                                        name="enable_pinterest"
                                        {{ old('enable_pinterest', $config?->enable_pinterest) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="enable_pinterest">Pinterest</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enable_linkedin"
                                        name="enable_linkedin"
                                        {{ old('enable_linkedin', $config?->enable_linkedin) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="enable_linkedin">LinkedIn</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enable_disqus"
                                        name="enable_disqus"
                                        {{ old('enable_disqus', $config?->enable_disqus) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="enable_disqus">Disqus</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enable_github"
                                        name="enable_github"
                                        {{ old('enable_github', $config?->enable_github) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="enable_github">GitHub</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="enable_gravatar"
                                        name="enable_gravatar"
                                        {{ old('enable_gravatar', $config?->enable_gravatar) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="enable_gravatar">Gravatar</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Google Adsense -->
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Google Adsense</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="adsense_platform_account">Adsense Platform Account</label>
                                <input type="text" name="adsense_platform_account" class="form-control"
                                    value="{{ old('adsense_platform_account', $config?->adsense_platform_account) }}">
                            </div>
                            <div class="form-group">
                                <label for="adsense_platform_domain">Adsense Platform Domain</label>
                                <input type="text" name="adsense_platform_domain" class="form-control"
                                    value="{{ old('adsense_platform_domain', $config?->adsense_platform_domain) }}">
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="adsense_non_personalized"
                                        name="adsense_non_personalized"
                                        {{ old('adsense_non_personalized', $config?->adsense_non_personalized) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="adsense_non_personalized">Non-personalized
                                        Ads</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional URLs -->
                    <div class="card card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">Additional URLs</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="blog_service_url">Blog Service URL</label>
                                <input type="text" name="blog_service_url" class="form-control"
                                    value="{{ old('blog_service_url', $config?->blog_service_url) }}">
                            </div>
                            <div class="form-group">
                                <label for="profile_url">Profile URL</label>
                                <input type="text" name="profile_url" class="form-control"
                                    value="{{ old('profile_url', $config?->profile_url) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Language Settings -->
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Cài đặt ngôn ngữ</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="months_name">Tên các tháng</label>
                                <textarea name="months_name" class="form-control" rows="3">{{ old('months_name', is_array($config?->months_name) ? json_encode($config?->months_name) : $config?->months_name) }}</textarea>
                                <small class="text-muted">JSON format</small>
                            </div>
                            <div class="form-group">
                                <label for="page_of_text">Page of Text</label>
                                <textarea name="page_of_text" class="form-control" rows="2">{{ old('page_of_text', is_array($config?->page_of_text) ? json_encode($config?->page_of_text) : $config?->page_of_text) }}</textarea>
                                <small class="text-muted">JSON format</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Hủy</a>
                    <button type="submit" class="btn btn-success float-right">Lưu thay đổi</button>
                </div>
            </div>
        </form>
    </section>
@endsection

@push('css')
    <style>
        .custom-switch {
            padding-left: 2.25rem;
        }

        .card-body {
            padding: 1.25rem;
        }
    </style>
@endpush

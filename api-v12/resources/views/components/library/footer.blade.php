{{-- resources/views/components/library/footer.blade.php
     Library 栏目全站 footer。
     当前为最简占位版本，后续按需扩展。
--}}
<footer class="footer footer-transparent d-print-none">
    <div class="container-xl">
        <div class="row text-center align-items-center">
            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                <ul class="list-inline list-inline-dots mb-0">
                    <li class="list-inline-item">
                        <a href="{{ route('library.home') }}" class="link-secondary">
                            WikiPāli
                        </a>
                    </li>
                    <li class="list-inline-item">
                        <a href="{{ route('library.download') }}" class="link-secondary">
                            下载
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>

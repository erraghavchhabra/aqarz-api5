
@if ($paginator->hasPages())
    <ul class="pagination pull-right">

        @if ($paginator->onFirstPage())
            <li class="disabled"><a href="#"><span class="glyphicon glyphicon-chevron-left"></span></a></li>

        @else
            <li><a href="#"><span class="glyphicon glyphicon-chevron-right"></span></a></li>

        @endif



        @foreach ($elements as $element)

            @if (is_string($element))
                <li class="disabled"><a href="#">{{ $element }}</a></li>
            @endif



            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active"><a href="#">{{ $page }}</a></li>
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach



        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Next →</a></li>
        @else
            <li class="disabled"><a href="#">Next →</a></li>
        @endif
    </ul>
@endif

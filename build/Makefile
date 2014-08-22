default: white

white: clean new whitecss extract fix pack

black: clean new blackcss extract fix pack

clean:
	@rm -rf book book.epub

new:
	@cp -r pristine book

whitecss:
	@cp css/white.css book/OEBPS/alexandria.css

blackcss:
	@cp css/black.css book/OEBPS/alexandria.css

extract:
	@php gen.php

fix:
	@./rebuild

pack:
	@cd book && zip -Xq ../book.epub mimetype
	@cd book && zip -Xr9Dq ../book.epub mimetype META-INF OEBPS


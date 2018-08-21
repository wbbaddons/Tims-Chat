FILES = $(shell find files -type f)
WCF_FILES = $(shell find files_wcf -type f)
JS_MODULE_FILES = $(shell find files_wcf/js/Bastelstu.be -type f)

all: be.bastelstu.chat.tar be.bastelstu.chat.tar.gz

be.bastelstu.chat.tar.gz: be.bastelstu.chat.tar
	gzip -9 < $< > $@

be.bastelstu.chat.tar: files.tar files_wcf.tar acptemplates.tar templates.tar *.xml LICENSE sql/*.sql language/*.xml
	tar cvf $@ --mtime="@$(shell git log -1 --format=%ct)" --owner=0 --group=0 --numeric-owner --exclude-vcs -- $^

files.tar: $(FILES)
files_wcf.tar: $(WCF_FILES) files_wcf/js/Bastelstu.be.Chat.min.js
acptemplates.tar: acptemplates/*.tpl
templates.tar: templates/*.tpl

%.tar:
	tar cvf $@ --mtime="@$(shell git log -1 --format=%ct)" --owner=0 --group=0 --numeric-owner --exclude-vcs -C $* -- $(^:$*/%=%)

files_wcf/js/Bastelstu.be.Chat.min.js: Bastelstu.be.Chat.babel.js
	yarn run terser --comments '/Copyright|stackoverflow/' -m -c pure_funcs=[console.debug] --verbose --timings -o $@ $^

Bastelstu.be.Chat.babel.js: Bastelstu.be.Chat.js .babelrc
	yarn run babel $< --out-file $@

Bastelstu.be.Chat.js: $(JS_MODULE_FILES)
	yarn run r.js -o require.build.js

clean:
	-rm -f files.tar
	-rm -f files_wcf.tar
	-rm -f templates.tar
	-rm -f acptemplates.tar
	-rm -f Bastelstu.be.Chat.js
	-rm -f Bastelstu.be.Chat.babel.js
	-rm -f files_wcf/js/Bastelstu.be.Chat.min.js

distclean: clean
	-rm -f be.bastelstu.chat.tar
	-rm -f be.bastelstu.chat.tar.gz

.PHONY: distclean clean

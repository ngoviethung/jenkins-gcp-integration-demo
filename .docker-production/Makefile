VERSION ?= v0.0.1
NAME ?= sdu01
REGISTRY ?= hungnv93

# Commands
docker: docker-build docker-push

docker-build:
	docker build -f Dockerfile --target cli -t ${REGISTRY}/${NAME}-cli:${VERSION} ..
	docker build -f Dockerfile --target cron -t ${REGISTRY}/${NAME}-cron:${VERSION} ..
	docker build -f Dockerfile --target fpm_server -t ${REGISTRY}/${NAME}-fpm_server:${VERSION} ..
	docker build -f Dockerfile --target web_server -t ${REGISTRY}/${NAME}-web_server:${VERSION} ..

docker-push:
	docker push ${REGISTRY}/${NAME}-cli:${VERSION}
	docker push ${REGISTRY}/${NAME}-cron:${VERSION}
	docker push ${REGISTRY}/${NAME}-fpm_server:${VERSION}
	docker push ${REGISTRY}/${NAME}-web_server:${VERSION}
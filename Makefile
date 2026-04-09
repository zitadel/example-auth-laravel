.PHONY: help check start

ifneq (,$(wildcard .env))
include .env
endif

help:
	@echo "Usage:"
	@echo "  make start   Start the development server"
	@echo "  make check   Verify required dependencies are installed"

check:
	@command -v php >/dev/null 2>&1 || { \
		echo "Error: PHP is not installed." >&2; \
		echo "" >&2; \
		echo "  Install it using your package manager:" >&2; \
		echo "    brew install php           # macOS" >&2; \
		echo "    sudo apt install php-cli   # Ubuntu/Debian" >&2; \
		exit 1; \
	}
	@command -v composer >/dev/null 2>&1 || { \
		echo "Error: Composer is not installed." >&2; \
		echo "" >&2; \
		echo "  Install it from https://getcomposer.org/download/" >&2; \
		exit 1; \
	}
	@test -f .env || { \
		echo "Error: Missing .env file." >&2; \
		echo "" >&2; \
		echo "  Copy the example file and fill in your Zitadel credentials:" >&2; \
		echo "    cp .env.example .env" >&2; \
		exit 1; \
	}

start: check
	composer install
	composer run dev

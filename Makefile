# Development and Build Tasks for Contentful WordPress Plugins

# Default task
.DEFAULT_GOAL := help

# Colors for output
BLUE := \033[0;34m
GREEN := \033[0;32m
RED := \033[0;31m
NC := \033[0m # No Color

# Plugin directories
PLUGINS := community-listings contentful-tables graphql-shortcode-support

.PHONY: help install install-dev clean test phpcs phpstan build release

help: ## Show this help message
	@echo "$(BLUE)Contentful WordPress Plugins - Development Commands$(NC)"
	@echo ""
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "$(GREEN)%-15s$(NC) %s\n", $$1, $$2}' $(MAKEFILE_LIST)

install: ## Install production dependencies for all plugins
	@echo "$(BLUE)Installing production dependencies...$(NC)"
	@for plugin in $(PLUGINS); do \
		echo "Installing dependencies for $$plugin..."; \
		(cd $$plugin && composer install --no-dev --optimize-autoloader); \
	done
	@echo "$(GREEN)✅ Production dependencies installed!$(NC)"

install-dev: ## Install development dependencies for all plugins
	@echo "$(BLUE)Installing development dependencies...$(NC)"
	@for plugin in $(PLUGINS); do \
		echo "Installing dev dependencies for $$plugin..."; \
		(cd $$plugin && composer install); \
	done
	@echo "$(GREEN)✅ Development dependencies installed!$(NC)"

clean: ## Clean vendor directories and caches
	@echo "$(BLUE)Cleaning vendor directories...$(NC)"
	@for plugin in $(PLUGINS); do \
		echo "Cleaning $$plugin..."; \
		rm -rf $$plugin/vendor $$plugin/composer.lock; \
	done
	@echo "$(GREEN)✅ Clean completed!$(NC)"

phpcs: ## Run PHP CodeSniffer on all plugins
	@echo "$(BLUE)Running PHPCS on all plugins...$(NC)"
	@for plugin in $(PLUGINS); do \
		echo "Checking $$plugin..."; \
		(cd $$plugin && composer run phpcs); \
	done
	@echo "$(GREEN)✅ PHPCS completed!$(NC)"

phpcs-fix: ## Fix PHP CodeSniffer issues automatically
	@echo "$(BLUE)Auto-fixing PHPCS issues...$(NC)"
	@for plugin in $(PLUGINS); do \
		echo "Fixing $$plugin..."; \
		(cd $$plugin && ./vendor/bin/phpcbf --standard=phpcs.xml .); \
	done
	@echo "$(GREEN)✅ PHPCS fixes applied!$(NC)"

phpstan: ## Run PHPStan static analysis on all plugins
	@echo "$(BLUE)Running PHPStan on all plugins...$(NC)"
	@for plugin in $(PLUGINS); do \
		echo "Analyzing $$plugin..."; \
		(cd $$plugin && composer run phpstan); \
	done
	@echo "$(GREEN)✅ PHPStan analysis completed!$(NC)"

test: install-dev phpcs phpstan ## Run all quality assurance tests
	@echo "$(GREEN)✅ All tests passed!$(NC)"

build: clean install ## Build production-ready plugins
	@echo "$(BLUE)Building production plugins...$(NC)"
	@mkdir -p dist
	@for plugin in $(PLUGINS); do \
		echo "Building $$plugin..."; \
		version=$$(grep "Version:" $$plugin/$$plugin.php | sed 's/.*Version: *//'); \
		zip -r "dist/$$plugin-v$$version.zip" "$$plugin" \
			-x "$$plugin/.git*" "$$plugin/composer.phar" \
			"$$plugin/phpcs.xml" "$$plugin/phpstan.neon" \
			"$$plugin/composer.lock" "$$plugin/tests/*" \
			"$$plugin/.DS_Store" "$$plugin/vendor/*/tests/*" \
			"$$plugin/vendor/*/*/tests/*"; \
	done
	@echo "$(GREEN)✅ Build completed! Check dist/ directory.$(NC)"

release: test build ## Create release packages
	@echo "$(BLUE)Creating release packages...$(NC)"
	@mkdir -p releases
	@cp dist/*.zip releases/
	@echo "$(GREEN)✅ Release packages ready in releases/ directory!$(NC)"

dev-setup: ## Set up development environment
	@echo "$(BLUE)Setting up development environment...$(NC)"
	@if ! command -v composer &> /dev/null; then \
		echo "$(RED)❌ Composer not found. Please install Composer first.$(NC)"; \
		exit 1; \
	fi
	@if ! command -v php &> /dev/null; then \
		echo "$(RED)❌ PHP not found. Please install PHP 8.2+ first.$(NC)"; \
		exit 1; \
	fi
	@php --version | head -1
	@composer --version
	@echo "$(GREEN)✅ Development environment ready!$(NC)"

lint: ## Run linting on all plugins
	@echo "$(BLUE)Linting all plugins...$(NC)"
	@make phpcs
	@make phpstan
	@echo "$(GREEN)✅ Linting completed!$(NC)"

watch: ## Watch for changes and run tests automatically
	@echo "$(BLUE)Watching for changes... (Ctrl+C to stop)$(NC)"
	@while true; do \
		inotifywait -r -e modify,create,delete . --exclude='vendor|node_modules|\.git' 2>/dev/null || sleep 2; \
		echo "Changes detected, running tests..."; \
		make lint || true; \
		echo "Waiting for changes..."; \
	done

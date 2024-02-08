{
  system,
  pkgs,
  lib,
  config,
  chips,
  ...
}:
with lib; let
  vscodeSettings = pkgs.writeText "settings.json" (builtins.toJSON {
    "php.validate.executablePath" = "${pkgs.php}/bin/php";
    "files.exclude" = {
      "**/.chips" = true;
      "**/.task" = true;
    };
  });

  taskConfig = {
    tasks = {
      composer = {
        cmds = ["${pkgs.php.packages.composer}/bin/composer install"];
        generates = ["vendor/composer/installed.json" "vendor/autoload.php"];
        label = "Install Composer Dependencies";
        sources = ["composer.json" "composer.lock"];
      };
      node_modules = {
        cmds = ["${pkgs.nodePackages.pnpm}/bin/pnpm install"];
        generates = ["node_modules/.modules.yaml"];
        label = "Install Node.JS Dependencies";
        sources = ["package.json" "pnpm-lock.yaml"];
      };
    };
    version = "3";
  };
  taskConfigFile = pkgs.writeText "Taskfile.yml" (builtins.toJSON taskConfig);

  lefthookConfig = {
    pre-commit = {
      commands = {
        alejandra = {
          glob = "*.nix";
          run = "${pkgs.alejandra}/bin/alejandra --quiet {staged_files} && git add {staged_files}";
        };
        eslint = {
          exclude = "GraphQL/index.tsx";
          glob = "*.{js,ts,jsx,tsx}";
          run = "./node_modules/.bin/eslint --fix --max-warnings 0 {staged_files} && git add {staged_files}";
        };
        php-cs-fixer = {
          glob = "*.php";
          run = "./vendor/bin/php-cs-fixer fix {staged_files} && git add {staged_files}";
        };
        sort-json = {
          exclude = "package-lock.json";
          glob = "*.json";
          run = "./node_modules/.bin/prettier {staged_files} && git add {staged_files}";
        };
      };
      parallel = true;
    };
    pre-push = {
      commands = {
        build = {run = "${pkgs.go-task}/bin/task build";};
        eslint = {
          glob = "*.{js,ts,jsx,tsx}";
          run = "./node_modules/.bin/eslint --cache --max-warnings 0 .";
        };
        nix-build = {run = "nix build .#valhalla";};
        php-cs-fixer = {
          glob = "*.php";
          run = "./vendor/bin/php-cs-fixer fix --dry-run";
        };
        tsc = {
          glob = "*.{ts,tsx}";
          run = "tsc --noEmit --project tsconfig.json";
        };
      };
      parallel = true;
    };
  };

  leftHook = pkgs.writeText "lefthook.yml" (builtins.toJSON lefthookConfig);
in {
  imports = [
  ];
  options = with types; {
    project = {
    };
  };
  config = mkMerge [
    {
      devShell = {
        contents = with pkgs; [
          go-task
          lefthook
          alejandra
        ];
        environment = [
        ];
        shellHooks = ''
          ln -sf ${leftHook} lefthook.yml
          ln -sf ${taskConfigFile} Taskfile.yml
          # ln -sf ${vscodeSettings} .vscode/settings.json
          lefthook install
        '';
      };

      programs.nodejs.enable = true;
      programs.php.enable = true;
    }
  ];
}

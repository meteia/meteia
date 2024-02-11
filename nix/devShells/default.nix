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
        contents = with pkgs; [];
        environment = [
        ];
        shellHooks = ''
          # ln -sf ${vscodeSettings} .vscode/settings.json
        '';
      };

      programs.lefthook.enable = true;
      programs.taskfile.enable = true;
      programs.nodejs.enable = true;
      programs.php.enable = true;
    }
  ];
}

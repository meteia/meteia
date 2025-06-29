{
  system,
  pkgs,
  lib,
  config,
  chips,
  ...
}:
with lib; {
  imports = [];
  options = with types; {
    project = {};
  };
  config = mkMerge [
    {
      devShell = {
        contents = with pkgs; [];
        environment = [];
      };

      programs.lefthook.enable = true;
      programs.taskfile.enable = true;
      programs.nodejs.enable = true;
      programs.php = {
        pkg = pkgs.php84;
        extensions = {
          enabled,
          all,
          ...
        }:
          with all;
            enabled
            ++ [
              apcu
              igbinary
            ];
        enable = true;
      };
    }
  ];
}

{
  pkgs,
  lib,
  ...
}:
with lib; {
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
        extraConfig = ''
          output_buffering = 4096
          post_max_size = 100M
          upload_max_filesize = 100M
          variables_order = EGPCS
        '';
        extensions = {
          enabled,
          all,
          ...
        }:
          with all;
            enabled
            ++ [
              apcu
              event
              imagick
              igbinary
            ];
        enable = true;
      };
    }
  ];
}
